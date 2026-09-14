<?php

namespace App\Support;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

/**
 * The counts the app shell needs: how much is due across the whole garage, and
 * how many vehicles want a fresh odometer reading.
 *
 * Shared globally so a due item is visible from anywhere rather than only from
 * the garage — previously you had to go looking for it, which rather defeats
 * the point of a reminder.
 *
 * Deliberately computed rather than stored: progress depends on a PROJECTED
 * odometer, which moves with the clock, so a cached count would be wrong by
 * tomorrow. It costs two queries and some arithmetic over a handful of rows.
 */
readonly class GarageSummary
{
    private function __construct(
        public int $dueCount,
        public int $needsReadingCount,
        public int $vehicleCount,
    ) {}

    public static function for(User $user): self
    {
        $vehicles = $user->vehicles()->with('intervals.serviceType')->get();

        $due = 0;
        $needsReading = 0;

        foreach ($vehicles as $vehicle) {
            $gauges = VehicleGauges::for($vehicle);

            $due += $gauges->needingAttention()->count();

            if ($gauges->mileage->needsReading) {
                $needsReading++;
            }
        }

        return new self($due, $needsReading, $vehicles->count());
    }

    public static function empty(): self
    {
        return new self(0, 0, 0);
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'due_count' => $this->dueCount,
            'needs_reading_count' => $this->needsReadingCount,
            'vehicle_count' => $this->vehicleCount,
        ];
    }

    /**
     * @param  Collection<int, Vehicle>  $vehicles
     */
    public static function fromVehicles(Collection $vehicles): self
    {
        $due = 0;
        $needsReading = 0;

        foreach ($vehicles as $vehicle) {
            $gauges = VehicleGauges::for($vehicle);
            $due += $gauges->needingAttention()->count();

            if ($gauges->mileage->needsReading) {
                $needsReading++;
            }
        }

        return new self($due, $needsReading, $vehicles->count());
    }
}
