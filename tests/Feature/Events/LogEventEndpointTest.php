<?php

use App\Enums\EventType;
use App\Models\Event;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->vehicle = Vehicle::factory()->for($this->user)->create();
});

test('a fuel entry needs only odometer, gallons and total cost', function () {
    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'fuel',
        'odometer' => 10_000,
        'occurred_on' => '2026-09-01',
        'gallons' => 12.4,
        'cost' => '44.99',
        'full_tank' => true,
    ])->assertSessionHasNoErrors();

    $event = Event::firstOrFail();

    expect($event->type)->toBe(EventType::Fuel)
        ->and($event->cost_cents)->toBe(4499)
        ->and((float) $event->gallons)->toBe(12.4);
});

test('a service visit records its line items and resets intervals', function () {
    $oil = ServiceType::factory()->create();
    $tires = ServiceType::factory()->create();

    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'visit',
        'odometer' => 55_000,
        'occurred_on' => '2026-09-01',
        'location' => 'Corner Auto',
        'line_items' => [
            ['service_type_id' => $oil->id, 'cost' => '65.00'],
            ['service_type_id' => $tires->id, 'cost' => '25.00'],
        ],
    ])->assertSessionHasNoErrors();

    $event = Event::firstOrFail();

    expect($event->lineItems)->toHaveCount(2)
        ->and($event->lineItems->first()->cost_cents)->toBe(6500)
        ->and($this->vehicle->intervals()->count())->toBe(2);
});

test('a service visit requires at least one line item', function () {
    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'visit',
        'odometer' => 55_000,
        'occurred_on' => '2026-09-01',
    ])->assertSessionHasErrors('line_items');
});

test('an expense entry requires a category', function () {
    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'expense',
        'odometer' => 10_000,
        'occurred_on' => '2026-09-01',
        'cost' => '120.00',
    ])->assertSessionHasErrors('category');
});

test('a bare mileage entry is enough on its own', function () {
    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'odometer',
        'odometer' => 12_345,
        'occurred_on' => '2026-09-01',
    ])->assertSessionHasNoErrors();

    expect($this->vehicle->fresh()->last_odometer)->toBe(12_345);
});

test('a future date is rejected', function () {
    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'odometer',
        'odometer' => 12_345,
        'occurred_on' => now()->addDay()->toDateString(),
    ])->assertSessionHasErrors('occurred_on');
});

test('the odometer guardrail rejects a reading that goes backwards', function () {
    Event::factory()->for($this->vehicle)->on('2026-08-01', 50_000)->create();

    $this->post(route('vehicles.events.store', $this->vehicle), [
        'type' => 'odometer',
        'odometer' => 40_000,
        'occurred_on' => '2026-09-01',
    ])->assertSessionHasErrors('odometer');

    expect($this->vehicle->events()->count())->toBe(1);
});

test('an implausible jump is accepted once confirmed', function () {
    Event::factory()->for($this->vehicle)->on('2026-08-01', 50_000)->create();

    $payload = [
        'type' => 'odometer',
        'odometer' => 90_000,
        'occurred_on' => '2026-08-05',
    ];

    $this->post(route('vehicles.events.store', $this->vehicle), $payload)
        ->assertSessionHasErrors('odometer');

    $this->post(route('vehicles.events.store', $this->vehicle), [
        ...$payload,
        'confirm_odometer' => true,
    ])->assertSessionHasNoErrors();

    expect($this->vehicle->events()->count())->toBe(2);
});

test('a user cannot log an entry against another user vehicle', function () {
    $other = Vehicle::factory()->create();

    $this->post(route('vehicles.events.store', $other), [
        'type' => 'odometer',
        'odometer' => 10_000,
        'occurred_on' => '2026-09-01',
    ])->assertForbidden();

    expect(Event::count())->toBe(0);
});

test('the history page lists entries across all of the user vehicles', function () {
    $second = Vehicle::factory()->for($this->user)->create();

    Event::factory()->for($this->vehicle)->on('2026-08-01', 10_000)->create();
    Event::factory()->for($second)->on('2026-09-01', 20_000)->create();
    Event::factory()->for(Vehicle::factory()->create())->create();

    $this->get(route('history'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('History')
            ->where('events.total', 2));
});
