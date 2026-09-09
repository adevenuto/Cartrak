<?php

namespace App\Actions\Events;

use App\Enums\EventType;
use App\Enums\IntervalSource;
use App\Models\Event;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Writes one event and everything derived from it.
 *
 * Keeping this in one place is the point of the single-spine design: whatever
 * lane the user logged through, exactly two things must stay true afterwards —
 * the vehicle's mileage estimate inputs, and (for visits) the intervals that
 * the visit just reset.
 */
class LogEvent
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array{service_type_id: int, cost_cents?: int|null, notes?: string|null}>  $lineItems
     */
    public function handle(Vehicle $vehicle, array $attributes, array $lineItems = []): Event
    {
        return DB::transaction(function () use ($vehicle, $attributes, $lineItems): Event {
            $event = $vehicle->events()->create($attributes);

            if ($event->type === EventType::Fuel) {
                $this->computeMpg($vehicle, $event);
            }

            if ($event->type === EventType::Visit && $lineItems !== []) {
                $this->saveLineItems($vehicle, $event, $lineItems);
            }

            $this->refreshOdometer($vehicle);

            return $event->fresh(['lineItems']) ?? $event;
        });
    }

    /**
     * MPG is only meaningful between two full tanks: the delta since the last
     * full fill divided by the gallons it took to fill up again. A partial fill
     * or a first-ever fill leaves it null rather than guessing.
     */
    private function computeMpg(Vehicle $vehicle, Event $event): void
    {
        if ($event->full_tank !== true || ! is_numeric($event->gallons) || (float) $event->gallons <= 0.0) {
            return;
        }

        $previous = $vehicle->events()
            ->whereKeyNot($event->getKey())
            ->where('type', EventType::Fuel)
            ->where('full_tank', true)
            ->where('occurred_on', '<=', $event->occurred_on)
            ->orderByDesc('occurred_on')
            ->orderByDesc('odometer')
            ->first();

        if ($previous === null) {
            return;
        }

        $miles = $event->odometer - $previous->odometer;

        if ($miles <= 0) {
            return;
        }

        $event->forceFill([
            'mpg' => round($miles / (float) $event->gallons, 2),
        ])->save();
    }

    /**
     * A visit's line items are the jobs done. Each one resets its interval's
     * last_done_* — which is why a single shop visit can reset several gauges —
     * and an interval row is created on demand if the vehicle had none yet.
     *
     * @param  array<int, array{service_type_id: int, cost_cents?: int|null, notes?: string|null}>  $lineItems
     */
    private function saveLineItems(Vehicle $vehicle, Event $event, array $lineItems): void
    {
        $serviceTypes = ServiceType::query()
            ->whereIn('id', array_column($lineItems, 'service_type_id'))
            ->get()
            ->keyBy('id');

        foreach ($lineItems as $item) {
            $serviceType = $serviceTypes->get($item['service_type_id']);

            if ($serviceType === null) {
                continue;
            }

            $event->lineItems()->create([
                'service_type_id' => $serviceType->id,
                'cost_cents' => $item['cost_cents'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);

            $this->markIntervalDone($vehicle, $serviceType, $event);
        }
    }

    private function markIntervalDone(Vehicle $vehicle, ServiceType $serviceType, Event $event): void
    {
        /** @var VehicleInterval $interval */
        $interval = $vehicle->intervals()->firstOrNew(
            ['service_type_id' => $serviceType->id],
        );

        if (! $interval->exists) {
            $interval->fill([
                'interval_months' => $serviceType->default_interval_months,
                'interval_miles' => $serviceType->default_interval_miles,
                'source' => IntervalSource::Default,
            ]);
        }

        // Only move last_done_* forward. Backfilling an older visit must not
        // rewind an interval that a more recent visit already reset.
        $isNewer = $interval->last_done_at === null
            || $event->occurred_on->greaterThanOrEqualTo($interval->last_done_at);

        if ($isNewer) {
            $interval->last_done_at = $event->occurred_on;
            $interval->last_done_odometer = $event->odometer;
        }

        $interval->save();
    }

    /**
     * The vehicle's last known reading is whichever event is newest by date,
     * recomputed rather than assumed — a backfilled event must not clobber a
     * newer one.
     *
     * avg_miles_per_day is deliberately left alone: mileage estimation is
     * Phase 2.
     */
    private function refreshOdometer(Vehicle $vehicle): void
    {
        $newest = $vehicle->events()
            ->orderByDesc('occurred_on')
            ->orderByDesc('odometer')
            ->first();

        if ($newest === null) {
            return;
        }

        $vehicle->forceFill([
            'last_odometer' => $newest->odometer,
            'last_odometer_at' => Carbon::parse($newest->occurred_on)->toDateString(),
        ])->save();
    }
}
