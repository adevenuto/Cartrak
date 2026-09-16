<?php

use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use Illuminate\Support\Carbon;

/*
 * The vehicle detail screen renders straight from these fields, and none of
 * them are exercised by the gauge unit tests. A missing key here is a blank
 * metric cell or an empty switcher, which no other check would catch.
 */

function detailFor(array $intervals = []): array
{
    $user = User::factory()->create();

    $vehicle = Vehicle::factory()->for($user)->create([
        'last_odometer' => 15_100,
        'last_odometer_at' => Carbon::today()->subDays(7)->toDateString(),
        'avg_miles_per_day' => 21.0,
    ]);

    foreach ($intervals as $interval) {
        VehicleInterval::factory()->for($vehicle)
            ->for(ServiceType::factory())
            ->create($interval);
    }

    return [$user, $vehicle];
}

test('the switcher receives every vehicle in the garage', function () {
    [$user, $vehicle] = detailFor();
    Vehicle::factory()->count(2)->for($user)->create();

    $this->actingAs($user)
        ->get(route('vehicles.show', $vehicle))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page->has('vehicles', 3)
                ->where('vehicles.0.is_active', true)
                ->has('vehicles.0.spec')
                ->has('vehicles.0.color')
        );
});

test('the header receives its metric row', function () {
    [$user, $vehicle] = detailFor([[
        'interval_months' => 6,
        'interval_miles' => 5_000,
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
        'last_done_odometer' => 9_000,
    ]]);

    $this->actingAs($user)
        ->get(route('vehicles.show', $vehicle))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                // 21 mi/day x 30.44 days.
                ->where('vehicle.avg_miles_per_month', 639)
                ->where('vehicle.services_due_count', 1)
                ->where('vehicle.services_overdue_count', 1)
        );
});

test('gauges carry an uncapped percent and the binding interval', function () {
    [$user, $vehicle] = detailFor([[
        'interval_months' => 12,
        'interval_miles' => 5_000,
        'last_done_at' => Carbon::today()->subMonths(18)->toDateString(),
        'last_done_odometer' => 9_000,
    ]]);

    $this->actingAs($user)
        ->get(route('vehicles.show', $vehicle))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                // Long overdue: the dial clamps its arc, the NUMBER must not.
                ->where('gauges.0.percent', fn (int $p): bool => $p > 100)
                ->has('gauges.0.basis')
        );
});

test('avg per month is null rather than zero when there is no rate yet', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->for($user)->create([
        'last_odometer' => 15_100,
        'last_odometer_at' => Carbon::today()->toDateString(),
        'avg_miles_per_day' => null,
    ]);

    $this->actingAs($user)
        ->get(route('vehicles.show', $vehicle))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('vehicle.avg_miles_per_month', null));
});
