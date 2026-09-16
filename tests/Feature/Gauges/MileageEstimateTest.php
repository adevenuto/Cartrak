<?php

use App\Actions\Vehicles\RecalculateMileageRate;
use App\Models\Event;
use App\Models\Vehicle;
use App\Support\VehicleMileage;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->vehicle = Vehicle::factory()->create();
    $this->action = app(RecalculateMileageRate::class);
});

test('the rate is total miles over total days across the window', function () {
    Event::factory()->for($this->vehicle)->on(Carbon::today()->subDays(60)->toDateString(), 41_200)->create();
    Event::factory()->for($this->vehicle)->on(Carbon::today()->subDays(30)->toDateString(), 41_850)->create();
    Event::factory()->for($this->vehicle)->on(Carbon::today()->subDays(10)->toDateString(), 43_100)->create();

    // 1,900 miles over 50 days.
    expect($this->action->handle($this->vehicle))->toBe(38.0);
});

test('a single reading yields no rate, and therefore no projection', function () {
    $on = Carbon::today()->subDays(10)->toDateString();

    Event::factory()->for($this->vehicle)->on($on, 41_200)->create();
    $this->vehicle->forceFill(['last_odometer' => 41_200, 'last_odometer_at' => $on])->save();

    expect($this->action->handle($this->vehicle))->toBeNull();

    $mileage = VehicleMileage::for($this->vehicle->fresh());

    expect($mileage->projectedOdometer)->toBe(41_200)
        ->and($mileage->isProjected())->toBeFalse();
});

test('a road trip does not peg the rate for good', function () {
    // Three months of ordinary commuting, then two fill-ups a day apart.
    Event::factory()->for($this->vehicle)->on(Carbon::today()->subDays(80)->toDateString(), 40_000)->create();
    Event::factory()->for($this->vehicle)->on(Carbon::today()->subDays(2)->toDateString(), 42_000)->create();
    Event::factory()->for($this->vehicle)->on(Carbon::today()->subDay()->toDateString(), 42_400)->create();

    $rate = $this->action->handle($this->vehicle);

    // The naive "two most recent readings" rule would say 400 mi/day.
    expect($rate)->toBeLessThan(50.0)
        ->and($rate)->toBeGreaterThan(20.0);
});

test('several readings on one day do not imply a daily rate', function () {
    Event::factory()->for($this->vehicle)->on('2026-09-01', 41_200)->create();
    Event::factory()->for($this->vehicle)->on('2026-09-01', 41_260)->create();

    expect($this->action->handle($this->vehicle))->toBeNull();
});

test('the projection carries the last reading forward at the daily rate', function () {
    $vehicle = Vehicle::factory()->create([
        'last_odometer' => 43_100,
        'last_odometer_at' => Carbon::today()->subDays(10)->toDateString(),
        'avg_miles_per_day' => 38,
    ]);

    $mileage = VehicleMileage::for($vehicle);

    expect($mileage->projectedOdometer)->toBe(43_480)   // 43,100 + 38 x 10
        ->and($mileage->daysSinceReading)->toBe(10)
        ->and($mileage->isProjected())->toBeTrue();
});

test('a stale projection asks for a fresh reading', function () {
    $fresh = Vehicle::factory()->create([
        'last_odometer' => 40_000,
        'last_odometer_at' => Carbon::today()->subDays(5)->toDateString(),
        'avg_miles_per_day' => 20,        // 100 projected miles
    ]);

    $stale = Vehicle::factory()->create([
        'last_odometer' => 40_000,
        'last_odometer_at' => Carbon::today()->subDays(30)->toDateString(),
        'avg_miles_per_day' => 40,        // 1,200 projected miles
    ]);

    expect(VehicleMileage::for($fresh)->needsReading)->toBeFalse()
        ->and(VehicleMileage::for($stale)->needsReading)->toBeTrue();
});

test('a heavy driver goes stale faster than a light one', function () {
    $light = Vehicle::factory()->create([
        'last_odometer' => 40_000,
        'last_odometer_at' => Carbon::today()->subDays(20)->toDateString(),
        'avg_miles_per_day' => 5,
    ]);

    $heavy = Vehicle::factory()->create([
        'last_odometer' => 40_000,
        'last_odometer_at' => Carbon::today()->subDays(20)->toDateString(),
        'avg_miles_per_day' => 80,
    ]);

    // Same elapsed time, different confidence — which is the whole point of
    // triggering on projected miles rather than on a calendar.
    expect(VehicleMileage::for($light)->needsReading)->toBeFalse()
        ->and(VehicleMileage::for($heavy)->needsReading)->toBeTrue();
});
