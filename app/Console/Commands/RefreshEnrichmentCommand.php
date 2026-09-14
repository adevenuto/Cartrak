<?php

namespace App\Console\Commands;

use App\Jobs\DecodeVehicleVin;
use App\Jobs\FetchEpaFuelEconomy;
use App\Models\Vehicle;
use Illuminate\Console\Command;

/**
 * Backfills VIN decode and EPA figures onto vehicles that predate enrichment.
 *
 * Decode and the EPA lookup are dispatched when a vehicle is created or its VIN
 * changes, so a car added before those existed has neither. Same gap the
 * vehicles:seed-intervals command closes for schedules.
 *
 * Both jobs are queued and both are idempotent, so this is safe to re-run.
 */
class RefreshEnrichmentCommand extends Command
{
    protected $signature = 'vehicles:refresh-enrichment {--vehicle= : Only this vehicle id}';

    protected $description = 'Queue VIN decode and EPA fuel economy lookups for existing vehicles';

    public function handle(): int
    {
        $queued = 0;

        Vehicle::query()
            ->when($this->option('vehicle'), fn ($q, $id) => $q->whereKey($id))
            ->chunkById(100, function ($vehicles) use (&$queued): void {
                foreach ($vehicles as $vehicle) {
                    if (filled($vehicle->vin)) {
                        DecodeVehicleVin::dispatch($vehicle);
                    } else {
                        FetchEpaFuelEconomy::dispatch($vehicle);
                    }

                    $queued++;
                    $this->line("  queued {$vehicle->displayName()}");
                }
            });

        $this->info("Queued enrichment for {$queued} vehicle(s). Run a worker to process them.");

        return self::SUCCESS;
    }
}
