<?php

namespace App\Console\Commands;

use App\Actions\Vehicles\SeedVehicleIntervals;
use App\Models\Vehicle;
use Illuminate\Console\Command;

/**
 * Backfills maintenance schedules onto vehicles that predate schedule seeding.
 *
 * Vehicles created before gauges existed have no interval rows at all, so their
 * gauge cluster is empty and there is nothing to calibrate. Seeding is
 * idempotent — existing rows, including user overrides, are left untouched.
 */
class SeedVehicleIntervalsCommand extends Command
{
    protected $signature = 'vehicles:seed-intervals';

    protected $description = 'Give every vehicle a maintenance schedule, skipping any it already has';

    public function handle(SeedVehicleIntervals $seed): int
    {
        $seeded = 0;

        Vehicle::query()->withCount('intervals')->chunkById(100, function ($vehicles) use ($seed, &$seeded): void {
            foreach ($vehicles as $vehicle) {
                $before = $vehicle->intervals_count;

                $seed->handle($vehicle);

                $after = $vehicle->intervals()->count();

                if ($after > $before) {
                    $seeded++;
                    $this->line("  {$vehicle->displayName()}: +".($after - $before).' intervals');
                }
            }
        });

        $this->info($seeded === 0
            ? 'Every vehicle already has a schedule.'
            : "Seeded {$seeded} vehicle(s).");

        return self::SUCCESS;
    }
}
