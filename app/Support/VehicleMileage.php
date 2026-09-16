<?php

namespace App\Support;

use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

/**
 * The projected odometer, and how much to trust it.
 *
 * Gauges count down against a PROJECTED reading rather than the last recorded
 * one, so they keep moving between entries instead of freezing until the next
 * fill-up. The projection is just the last real reading plus the daily rate
 * times the days since — which means its error grows with staleness, and that
 * is exactly what decides when to ask the user for a fresh reading.
 */
readonly class VehicleMileage
{
    private function __construct(
        public ?int $lastOdometer,
        public ?CarbonInterface $lastOdometerAt,
        public ?float $milesPerDay,
        public int $projectedOdometer,
        public int $daysSinceReading,
        public int $projectedMilesSinceReading,
        public bool $needsReading,
    ) {}

    public static function for(Vehicle $vehicle, ?CarbonInterface $asOf = null): self
    {
        $asOf = $asOf ?? Date::today();
        $last = $vehicle->last_odometer;
        $lastAt = $vehicle->last_odometer_at;
        $rate = $vehicle->avg_miles_per_day === null
            ? null
            : (float) $vehicle->avg_miles_per_day;

        if ($last === null || $lastAt === null) {
            return new self(null, null, $rate, 0, 0, 0, false);
        }

        $days = max(0, (int) $lastAt->startOfDay()->diffInDays($asOf->startOfDay()));

        // No rate yet (a car with a single reading) means no projection — the
        // last real number is the honest answer, not a guess.
        $projectedMiles = $rate === null ? 0 : (int) round($rate * $days);

        return new self(
            lastOdometer: $last,
            lastOdometerAt: $lastAt,
            milesPerDay: $rate,
            projectedOdometer: $last + $projectedMiles,
            daysSinceReading: $days,
            projectedMilesSinceReading: $projectedMiles,
            needsReading: $projectedMiles >= Config::integer('vehicles.mileage.stale_miles'),
        );
    }

    /**
     * True once the projection is carrying real weight — used to label a gauge
     * as an estimate rather than a measurement.
     */
    public function isProjected(): bool
    {
        return $this->projectedMilesSinceReading > 0;
    }
}
