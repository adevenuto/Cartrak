<?php

use App\Actions\Recalls\CheckVehicleRecalls;
use App\Jobs\DecodeVehicleVin;
use App\Jobs\FetchEpaFuelEconomy;
use App\Models\Event;
use App\Models\Recall;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\RecallNotification;
use App\Services\Epa\FuelEconomyClient;
use App\Services\Nhtsa\NhtsaClient;
use App\Support\VehicleGauges;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

function vpicResponse(array $overrides = []): array
{
    return ['Results' => [[
        'ModelYear' => '2024',
        'Make' => 'SUBARU',
        'Model' => 'WRX',
        'Trim' => 'Premium',
        'DisplacementL' => '2.4',
        'EngineCylinders' => '4',
        'EngineHP' => '271',
        'PlantCity' => 'GUNMA',
        'ErrorCode' => '0',
        ...$overrides,
    ]]];
}

beforeEach(function () {
    Notification::fake();
    $this->user = User::factory()->create();
});

test('decoding a vin fills empty fields and caches the raw response', function () {
    Http::fake(['*vpic*' => Http::response(vpicResponse())]);

    $vehicle = Vehicle::factory()->for($this->user)->create([
        'vin' => 'JF1VBAH6XR9800897',
        'year' => null, 'make' => null, 'model' => null, 'trim' => null, 'engine' => null,
    ]);

    (new DecodeVehicleVin($vehicle))->handle(app(NhtsaClient::class));

    $vehicle->refresh();

    expect($vehicle->year)->toBe(2024)
        ->and($vehicle->make)->toBe('Subaru')      // vPIC shouts; we title-case
        ->and($vehicle->model)->toBe('WRX')
        ->and($vehicle->engine)->toBe('2.4L 4cyl 271hp')
        ->and($vehicle->decoded_specs)->toHaveKey('PlantCity');
});

test('decoding never overwrites what the user typed', function () {
    Http::fake(['*vpic*' => Http::response(vpicResponse())]);

    $vehicle = Vehicle::factory()->for($this->user)->create([
        'vin' => 'JF1VBAH6XR9800897',
        'make' => 'Subaru', 'model' => 'WRX TR', 'trim' => 'my own note',
    ]);

    (new DecodeVehicleVin($vehicle))->handle(app(NhtsaClient::class));

    // Someone who typed a correction is probably right about their own car.
    expect($vehicle->refresh()->model)->toBe('WRX TR')
        ->and($vehicle->trim)->toBe('my own note');
});

test('a vpic outage leaves the vehicle untouched', function () {
    Http::fake(['*' => Http::response(null, 503)]);

    $vehicle = Vehicle::factory()->for($this->user)->create([
        'vin' => 'JF1VBAH6XR9800897', 'make' => 'Subaru', 'year' => 2024,
    ]);

    (new DecodeVehicleVin($vehicle))->handle(app(NhtsaClient::class));

    // Enrichment is never a hard dependency — no exception, no damage.
    expect($vehicle->refresh()->make)->toBe('Subaru')
        ->and($vehicle->decoded_specs)->toBeNull();
});

test('a new recall is stored and notified once', function () {
    Http::fake(['*recallsByVehicle*' => Http::response(['results' => [[
        'NHTSACampaignNumber' => '24V123000',
        'Component' => 'FUEL SYSTEM',
        'Summary' => 'The fuel pump may fail.',
        'Remedy' => 'Dealers will replace the pump.',
        'ReportReceivedDate' => '2024-03-01',
    ]]])]);

    $vehicle = Vehicle::factory()->for($this->user)->create(['vin' => 'JF1VBAH6XR9800897']);
    $action = app(CheckVehicleRecalls::class);

    expect($action->handle($vehicle))->toBe(1)
        ->and($vehicle->recalls()->count())->toBe(1);

    // A weekly re-check must upsert, not re-announce.
    expect($action->handle($vehicle))->toBe(0)
        ->and($vehicle->recalls()->count())->toBe(1);

    Notification::assertSentTimes(RecallNotification::class, 1);
});

test('a recall outage never deletes a recall already shown', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create(['vin' => 'JF1VBAH6XR9800897']);
    Recall::factory()->for($vehicle)->create(['campaign_number' => '24V123000']);

    Http::fake(['*' => Http::response(['results' => []])]);

    app(CheckVehicleRecalls::class)->handle($vehicle);

    // An outage must not silently erase a safety notice.
    expect($vehicle->recalls()->count())->toBe(1);
});

test('a vehicle with no vin is skipped entirely', function () {
    Http::fake();

    $vehicle = Vehicle::factory()->for($this->user)->create(['vin' => null]);

    expect(app(CheckVehicleRecalls::class)->handle($vehicle))->toBe(0);

    Http::assertNothingSent();
});

test('the epa lookup caches sticker mpg', function () {
    Http::fake([
        '*menu/options*' => Http::response(['menuItem' => [
            ['text' => 'Man 6-spd, 4 cyl, 2.4 L, Turbo', 'value' => '47580'],
        ]]),
        '*vehicle/47580*' => Http::response([
            'city08' => 19, 'highway08' => 26, 'comb08' => 22,
        ]),
    ]);

    $vehicle = Vehicle::factory()->for($this->user)->create([
        'year' => 2024, 'make' => 'Subaru', 'model' => 'WRX',
    ]);

    (new FetchEpaFuelEconomy($vehicle))->handle(app(FuelEconomyClient::class));

    $vehicle->refresh();

    expect($vehicle->epa_mpg_combined)->toBe(22)
        ->and($vehicle->epa_mpg_city)->toBe(19)
        ->and($vehicle->epa_mpg_highway)->toBe(26);
});

test('an epa miss leaves the vehicle untouched', function () {
    Http::fake(['*' => Http::response(['menuItem' => []])]);

    $vehicle = Vehicle::factory()->for($this->user)->create([
        'year' => 1998, 'make' => 'Obscure', 'model' => 'Thing',
    ]);

    (new FetchEpaFuelEconomy($vehicle))->handle(app(FuelEconomyClient::class));

    // Plenty of vehicles simply are not in the dataset; that is not an error.
    expect($vehicle->refresh()->epa_mpg_combined)->toBeNull();
});

test('the fuel benchmark needs a few readings before it judges', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create(['epa_mpg_combined' => 22]);

    // Two readings is not a benchmark — it says more about the pump's cut-off.
    Event::factory()->for($vehicle)->fuel()->count(2)->create(['mpg' => 25]);

    $benchmark = VehicleGauges::fuelBenchmark($vehicle);

    expect($benchmark['actual'])->toBeNull()
        ->and($benchmark['sticker'])->toBe(22);

    Event::factory()->for($vehicle)->fuel()->create(['mpg' => 25]);

    $benchmark = VehicleGauges::fuelBenchmark($vehicle->fresh());

    expect($benchmark['actual'])->toBe(25.0)
        ->and($benchmark['delta_percent'])->toBe(14.0);
});
