<?php

use App\Enums\EventType;
use App\Models\User;
use App\Models\Vehicle;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('the garage lists only the signed-in user vehicles', function () {
    Vehicle::factory()->for($this->user)->create(['make' => 'Toyota', 'model' => 'RAV4']);
    Vehicle::factory()->create(['make' => 'Someone', 'model' => 'Else']);

    $this->get(route('garage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Garage')
            ->has('vehicles', 1)
            ->where('vehicles.0.make', 'Toyota'));
});

test('adding a vehicle records its starting odometer as an event', function () {
    $response = $this->post(route('vehicles.store'), [
        'nickname' => 'Daily',
        'make' => 'Toyota',
        'model' => 'RAV4',
        'year' => 2019,
        'odometer' => 47_320,
    ]);

    $vehicle = Vehicle::firstOrFail();

    // Straight into quick calibrate, so the gauges start from something real.
    $response->assertRedirect(route('vehicles.calibrate', $vehicle));

    expect($vehicle->user_id)->toBe($this->user->id)
        ->and($vehicle->last_odometer)->toBe(47_320)
        ->and($vehicle->events()->count())->toBe(1)
        ->and($vehicle->events()->first()->type)->toBe(EventType::Odometer);
});

test('the odometer is required when adding a vehicle', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
    ])->assertSessionHasErrors('odometer');

    expect(Vehicle::count())->toBe(0);
});

test('an invalid vin is rejected', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
        'odometer' => 1000,
        'vin' => 'IOQ00000000000000',
    ])->assertSessionHasErrors('vin');
});

test('a vin is stored uppercased', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
        'odometer' => 1000,
        'vin' => '4t1bf1fk5cu123456',
    ]);

    expect(Vehicle::firstOrFail()->vin)->toBe('4T1BF1FK5CU123456');
});

test('a user cannot view another user vehicle', function () {
    $other = Vehicle::factory()->create();

    $this->get(route('vehicles.show', $other))->assertForbidden();
});

test('a user cannot delete another user vehicle', function () {
    $other = Vehicle::factory()->create();

    $this->delete(route('vehicles.destroy', $other))->assertForbidden();

    expect(Vehicle::whereKey($other->id)->exists())->toBeTrue();
});

test('a user can delete their own vehicle', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create();

    $this->delete(route('vehicles.destroy', $vehicle))->assertRedirect(route('garage'));

    expect(Vehicle::whereKey($vehicle->id)->exists())->toBeFalse();
});

test('the vehicle page lists its events newest first', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create();

    $this->post(route('vehicles.events.store', $vehicle), [
        'type' => 'odometer', 'odometer' => 10_000, 'occurred_on' => '2026-01-01',
    ]);
    $this->post(route('vehicles.events.store', $vehicle), [
        'type' => 'odometer', 'odometer' => 12_000, 'occurred_on' => '2026-06-01',
    ]);

    $this->get(route('vehicles.show', $vehicle))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vehicles/Show')
            ->has('events', 2)
            ->where('events.0.odometer', 12_000)
            ->where('events.1.odometer', 10_000));
});

test('a blank year is stored as null rather than rejected', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
        'year' => '',
        'odometer' => 1000,
    ])->assertSessionHasNoErrors();

    expect(Vehicle::firstOrFail()->year)->toBeNull();
});

test('a paint colour is stored and normalised to lowercase', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
        'odometer' => 1000,
        'color' => '#1F4E9C',
    ])->assertSessionHasNoErrors();

    expect(Vehicle::firstOrFail()->color)->toBe('#1f4e9c');
});

test('an invalid colour is rejected', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
        'odometer' => 1000,
        'color' => 'blue',
    ])->assertSessionHasErrors('color');
});

test('a vehicle can be saved without a colour', function () {
    $this->post(route('vehicles.store'), [
        'make' => 'Toyota',
        'model' => 'RAV4',
        'odometer' => 1000,
        'color' => '',
    ])->assertSessionHasNoErrors();

    expect(Vehicle::firstOrFail()->color)->toBeNull();
});

test('the garage exposes each vehicle colour for its dot', function () {
    Vehicle::factory()->for($this->user)->create(['color' => '#b31d26']);

    $this->get(route('garage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('vehicles.0.color', '#b31d26'));
});
