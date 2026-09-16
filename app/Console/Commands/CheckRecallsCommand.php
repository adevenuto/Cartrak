<?php

namespace App\Console\Commands;

use App\Actions\Recalls\CheckVehicleRecalls;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class CheckRecallsCommand extends Command
{
    protected $signature = 'recalls:check {--vehicle= : Check a single vehicle by id}';

    protected $description = 'Refresh NHTSA recalls for every vehicle with a VIN';

    public function handle(CheckVehicleRecalls $check): int
    {
        $found = 0;
        $checked = 0;

        Vehicle::query()
            ->whereNotNull('vin')
            ->when($this->option('vehicle'), fn ($q, $id) => $q->whereKey($id))
            ->with('user')
            ->chunkById(50, function ($vehicles) use ($check, &$found, &$checked): void {
                foreach ($vehicles as $vehicle) {
                    $checked++;
                    $new = $check->handle($vehicle);

                    if ($new > 0) {
                        $found += $new;
                        $this->line("  {$vehicle->displayName()}: {$new} new recall(s)");
                    }
                }
            });

        $this->info("Checked {$checked} vehicle(s); {$found} new recall(s).");

        return self::SUCCESS;
    }
}
