<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Services\Nhtsa\NhtsaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Fills in a vehicle's specs from its VIN, in the background.
 *
 * Queued rather than inline because vPIC can take several seconds, and adding a
 * car is the flow the brief wants to feel instant. The car is already saved and
 * usable before this runs; if it never runs, nothing breaks.
 *
 * Only EMPTY fields are filled. Someone who typed "WRX" when vPIC says
 * "WRX Limited" may well be right, and silently overwriting what a person
 * entered is a good way to lose their trust in everything else the app says.
 */
class DecodeVehicleVin implements ShouldQueue
{
    use Queueable;

    /** vPIC is slow but not flaky; a couple of goes is plenty. */
    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Vehicle $vehicle) {}

    public function handle(NhtsaClient $nhtsa): void
    {
        $vin = $this->vehicle->vin;

        if ($vin === null || $vin === '') {
            return;
        }

        $decoded = $nhtsa->decodeVin($vin);

        if ($decoded === null) {
            return;
        }

        $fill = [];

        foreach ($decoded['specs'] as $field => $value) {
            if (blank($this->vehicle->{$field})) {
                $fill[$field] = $value;
            }
        }

        // The full response is cached regardless: Phase 6's OEM schedules and
        // the EPA benchmark both want fields the UI never shows.
        $fill['decoded_specs'] = $decoded['raw'];

        $this->vehicle->forceFill($fill)->save();

        // Now that year/make/model are known, the sticker MPG can be looked up.
        FetchEpaFuelEconomy::dispatch($this->vehicle);
    }
}
