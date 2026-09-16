<?php

use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use App\Support\GarageSummary;
use Illuminate\Support\Carbon;

/*
 * The rail's "Due next" list is shared globally, so it has to hold up on every
 * page rather than only where it was built. These lock the contract the rail
 * renders against.
 */

function vehicleWithIntervals(User $user, array $intervals): Vehicle
{
    $vehicle = Vehicle::factory()->for($user)->create([
        'last_odometer' => 50_000,
        'last_odometer_at' => Carbon::today()->toDateString(),
    ]);

    foreach ($intervals as $interval) {
        VehicleInterval::factory()->for($vehicle)
            ->for(ServiceType::factory())
            ->create($interval);
    }

    return $vehicle;
}

test('due next is shared with every page', function () {
    $user = User::factory()->create();

    vehicleWithIntervals($user, [[
        'interval_months' => 6,
        'interval_miles' => 5_000,
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
        'last_done_odometer' => 44_000,
    ]]);

    $this->actingAs($user)
        ->get(route('garage'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page->has('garage.due_next', 1)
                ->where('garage.due_next.0.percent', fn ($p) => $p >= 100)
        );
});

test('due next never lists more than three', function () {
    $user = User::factory()->create();

    vehicleWithIntervals($user, array_fill(0, 5, [
        'interval_months' => 6,
        'interval_miles' => 5_000,
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
        'last_done_odometer' => 44_000,
    ]));

    expect(GarageSummary::for($user->fresh())->dueNext)->toHaveCount(3);
});

test('due next excludes uncalibrated intervals', function () {
    $user = User::factory()->create();

    // No last_done_* means no baseline, so there is nothing to rank.
    vehicleWithIntervals($user, [[
        'interval_months' => 6,
        'interval_miles' => 5_000,
        'last_done_at' => null,
        'last_done_odometer' => null,
    ]]);

    expect(GarageSummary::for($user->fresh())->dueNext)->toBeEmpty();
});

test('due next is ordered by how close to due each item is', function () {
    $user = User::factory()->create();

    vehicleWithIntervals($user, [
        [
            'interval_months' => 12,
            'interval_miles' => 100_000,
            'last_done_at' => Carbon::today()->subMonth()->toDateString(),
            'last_done_odometer' => 49_000,
        ],
        [
            'interval_months' => 6,
            'interval_miles' => 5_000,
            'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
            'last_done_odometer' => 44_000,
        ],
    ]);

    $percents = array_map(
        fn ($item) => $item->percent,
        GarageSummary::for($user->fresh())->dueNext,
    );

    expect($percents)->toBe(array_values(collect($percents)->sortDesc()->all()));
    expect($percents[0])->toBeGreaterThan($percents[1]);
});
