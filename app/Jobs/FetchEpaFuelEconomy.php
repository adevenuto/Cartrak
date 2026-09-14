<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Services\Epa\FuelEconomyClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Caches the EPA sticker MPG so real economy has something to be measured
 * against.
 *
 * Queued and entirely optional: plenty of vehicles are not in the EPA dataset
 * at all, and a miss simply means the benchmark is not shown.
 */
class FetchEpaFuelEconomy implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Vehicle $vehicle) {}

    public function handle(FuelEconomyClient $epa): void
    {
        $vehicle = $this->vehicle;

        if ($vehicle->year === null || blank($vehicle->make) || blank($vehicle->model)) {
            return;
        }

        $figures = $epa->lookup($vehicle->year, $vehicle->make, $vehicle->model, $vehicle->trim);

        if ($figures === null) {
            return;
        }

        $vehicle->forceFill([
            'epa_mpg_city' => $figures['city'],
            'epa_mpg_highway' => $figures['highway'],
            'epa_mpg_combined' => $figures['combined'],
        ])->save();
    }
}
