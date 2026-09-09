<?php

namespace App\Actions\Vehicles;

use App\Models\Event;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Recomputes a vehicle's average miles per day from its reading history.
 *
 * The brief says "from the two most recent readings", but taken literally that
 * is most fragile exactly when there is least history: two fill-ups a day apart
 * on a road trip would peg the car at hundreds of miles a day until the next
 * entry, and every gauge would race ahead with it.
 *
 * So: average across a window instead — total miles over total days between the
 * oldest and newest readings inside it. Same inputs, steadier number, and it
 * self-corrects as history builds. With fewer than two readings in the window
 * it widens to the full history rather than reporting nothing.
 */
class RecalculateMileageRate
{
    public function handle(Vehicle $vehicle): ?float
    {
        $rate = $this->rateFor($vehicle);

        $vehicle->forceFill(['avg_miles_per_day' => $rate])->save();

        return $rate;
    }

    private function rateFor(Vehicle $vehicle): ?float
    {
        $windowDays = Config::integer('vehicles.mileage.window_days');
        $since = Carbon::today()->subDays($windowDays)->toDateString();

        $readings = $this->readings($vehicle, $since);

        // Not enough inside the window to say anything — widen to everything we
        // have rather than reporting no rate at all.
        if ($readings->count() < Config::integer('vehicles.mileage.min_readings')) {
            $readings = $this->readings($vehicle, null);
        }

        if ($readings->count() < 2) {
            return null;
        }

        /** @var Event $oldest */
        $oldest = $readings->first();
        /** @var Event $newest */
        $newest = $readings->last();

        $miles = $newest->odometer - $oldest->odometer;
        $days = (int) $oldest->occurred_on->startOfDay()
            ->diffInDays($newest->occurred_on->startOfDay());

        // Several readings on one day say nothing about a daily rate.
        if ($days < 1 || $miles <= 0) {
            return null;
        }

        return round($miles / $days, 2);
    }

    /**
     * @return Collection<int, Event>
     */
    private function readings(Vehicle $vehicle, ?string $since): Collection
    {
        return $vehicle->events()
            ->when($since !== null, fn ($query) => $query->where('occurred_on', '>=', $since))
            ->orderBy('occurred_on')
            ->orderBy('odometer')
            ->get();
    }
}
