<?php

namespace App\Actions\Vehicles;

use App\Enums\IntervalSource;
use App\Models\ServiceType;
use App\Models\Vehicle;

/**
 * Gives a new vehicle its full maintenance schedule from the global catalogue.
 *
 * Until now an interval row only appeared once a service was logged against it,
 * which meant a brand-new car had no gauges at all. Seeding up front means the
 * cluster exists immediately — every item uncalibrated until the owner says
 * when it was last done, which is what the quick-calibrate flow is for.
 *
 * Existing rows are left alone: a user override must survive this being run
 * again.
 */
class SeedVehicleIntervals
{
    /** The gauge wall shows three large dials (design doc §6). */
    private const PINNED_LIMIT = 3;

    public function handle(Vehicle $vehicle): void
    {
        $existing = $vehicle->intervals()->pluck('service_type_id')->all();

        // Continue the vehicle's existing order rather than restarting at 0, so
        // re-running this cannot collide with positions already in use.
        $position = $vehicle->intervals()->exists()
            ? (int) $vehicle->intervals()->max('position') + 1
            : 0;

        $pinned = $vehicle->intervals()->where('is_pinned', true)->count();

        $types = ServiceType::query()
            ->whereNotIn('id', $existing)
            ->orderBy('sort_order')
            ->get();

        foreach ($types as $type) {
            $isActive = $type->default_interval_months !== null
                || $type->default_interval_miles !== null;

            // Open a new vehicle with a full gauge wall rather than an empty one.
            $shouldPin = $isActive && $pinned < self::PINNED_LIMIT;

            if ($shouldPin) {
                $pinned++;
            }

            $vehicle->intervals()->create([
                'service_type_id' => $type->id,
                'position' => $position++,
                'is_pinned' => $shouldPin,
                'interval_months' => $type->default_interval_months,
                'interval_miles' => $type->default_interval_miles,
                'source' => IntervalSource::Default,
                // Deliberately null: we do not know when this was last done, and
                // guessing would tell someone their oil is overdue when they
                // changed it last week.
                'last_done_at' => null,
                'last_done_odometer' => null,
                // A type with neither axis ("Other Service") can never be due,
                // so it starts inactive rather than cluttering the cluster.
                'is_active' => $isActive,
            ]);
        }
    }
}
