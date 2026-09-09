<?php

use App\Actions\Events\LogEvent;
use App\Enums\EventType;
use App\Models\ServiceType;
use App\Models\Vehicle;

beforeEach(function () {
    $this->action = app(LogEvent::class);
    $this->vehicle = Vehicle::factory()->create();
});

test('logging an event updates the vehicle last known reading', function () {
    $this->action->handle($this->vehicle, [
        'type' => EventType::Odometer,
        'odometer' => 42_000,
        'occurred_on' => '2026-09-01',
    ]);

    $this->vehicle->refresh();

    expect($this->vehicle->last_odometer)->toBe(42_000)
        ->and($this->vehicle->last_odometer_at->toDateString())->toBe('2026-09-01');
});

test('backfilling an older event does not rewind the last known reading', function () {
    $this->action->handle($this->vehicle, [
        'type' => EventType::Odometer,
        'odometer' => 42_000,
        'occurred_on' => '2026-09-01',
    ]);

    $this->action->handle($this->vehicle, [
        'type' => EventType::Odometer,
        'odometer' => 30_000,
        'occurred_on' => '2025-01-01',
    ]);

    $this->vehicle->refresh();

    expect($this->vehicle->last_odometer)->toBe(42_000);
});

test('mpg is computed between two full tanks', function () {
    $this->action->handle($this->vehicle, [
        'type' => EventType::Fuel,
        'odometer' => 10_000,
        'occurred_on' => '2026-08-01',
        'gallons' => 12.0,
        'full_tank' => true,
        'cost_cents' => 4200,
    ]);

    $second = $this->action->handle($this->vehicle, [
        'type' => EventType::Fuel,
        'odometer' => 10_300,
        'occurred_on' => '2026-08-15',
        'gallons' => 10.0,
        'full_tank' => true,
        'cost_cents' => 3800,
    ]);

    // 300 miles on 10 gallons.
    expect((float) $second->fresh()->mpg)->toBe(30.0);
});

test('mpg is null on the first fill up and on partial fills', function () {
    $first = $this->action->handle($this->vehicle, [
        'type' => EventType::Fuel,
        'odometer' => 10_000,
        'occurred_on' => '2026-08-01',
        'gallons' => 12.0,
        'full_tank' => true,
    ]);

    $partial = $this->action->handle($this->vehicle, [
        'type' => EventType::Fuel,
        'odometer' => 10_200,
        'occurred_on' => '2026-08-10',
        'gallons' => 5.0,
        'full_tank' => false,
    ]);

    expect($first->fresh()->mpg)->toBeNull()
        ->and($partial->fresh()->mpg)->toBeNull();
});

test('a visit with several line items resets several intervals at once', function () {
    $oil = ServiceType::factory()->create(['key' => 'oil', 'default_interval_miles' => 5000, 'default_interval_months' => 6]);
    $tires = ServiceType::factory()->create(['key' => 'tires', 'default_interval_miles' => 6000, 'default_interval_months' => 6]);

    $visit = $this->action->handle($this->vehicle, [
        'type' => EventType::Visit,
        'odometer' => 55_000,
        'occurred_on' => '2026-09-01',
        'location' => 'Corner Auto',
    ], [
        ['service_type_id' => $oil->id, 'cost_cents' => 6500],
        ['service_type_id' => $tires->id, 'cost_cents' => 2500],
    ]);

    expect($visit->lineItems)->toHaveCount(2);

    $intervals = $this->vehicle->intervals()->get()->keyBy('service_type_id');

    expect($intervals)->toHaveCount(2)
        ->and($intervals[$oil->id]->last_done_odometer)->toBe(55_000)
        ->and($intervals[$oil->id]->last_done_at->toDateString())->toBe('2026-09-01')
        ->and($intervals[$oil->id]->interval_miles)->toBe(5000)
        ->and($intervals[$tires->id]->last_done_odometer)->toBe(55_000);
});

test('backfilling an older visit does not rewind an interval already reset by a newer one', function () {
    $oil = ServiceType::factory()->create(['key' => 'oil']);

    $this->action->handle($this->vehicle, [
        'type' => EventType::Visit,
        'odometer' => 55_000,
        'occurred_on' => '2026-09-01',
    ], [['service_type_id' => $oil->id]]);

    $this->action->handle($this->vehicle, [
        'type' => EventType::Visit,
        'odometer' => 40_000,
        'occurred_on' => '2025-06-01',
    ], [['service_type_id' => $oil->id]]);

    $interval = $this->vehicle->intervals()->firstOrFail();

    expect($interval->last_done_odometer)->toBe(55_000)
        ->and($interval->last_done_at->toDateString())->toBe('2026-09-01');
});
