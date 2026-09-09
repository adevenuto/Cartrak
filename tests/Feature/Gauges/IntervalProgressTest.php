<?php

use App\Enums\BindingAxis;
use App\Enums\GaugeStatus;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use App\Support\IntervalProgress;
use App\Support\VehicleMileage;
use Illuminate\Support\Carbon;

function progressFor(array $interval, array $vehicle = []): IntervalProgress
{
    $car = Vehicle::factory()->create([
        'last_odometer' => $vehicle['odometer'] ?? 50_000,
        'last_odometer_at' => $vehicle['on'] ?? Carbon::today()->toDateString(),
        'avg_miles_per_day' => $vehicle['rate'] ?? null,
    ]);

    $row = VehicleInterval::factory()->for($car)
        ->for(ServiceType::factory())
        ->create($interval);

    return IntervalProgress::for($row, VehicleMileage::for($car));
}

test('an interval with no history is uncalibrated, not overdue', function () {
    $progress = progressFor([
        'interval_months' => 6,
        'interval_miles' => 5000,
        'last_done_at' => null,
        'last_done_odometer' => null,
    ]);

    expect($progress->status)->toBe(GaugeStatus::Uncalibrated)
        ->and($progress->axis)->toBe(BindingAxis::None)
        ->and($progress->label())->toBe('Not set');
});

test('mileage binds when miles run out first', function () {
    // 4,500 of 5,000 miles used; only 1 month of 6 elapsed.
    $progress = progressFor([
        'interval_months' => 6,
        'interval_miles' => 5000,
        'last_done_at' => Carbon::today()->subDays(30)->toDateString(),
        'last_done_odometer' => 45_500,
    ]);

    expect($progress->axis)->toBe(BindingAxis::Mileage)
        ->and($progress->status)->toBe(GaugeStatus::Soon)
        ->and($progress->milesRemaining)->toBe(500)
        ->and($progress->label())->toBe('500 mi left');
});

test('time binds when the calendar runs out first', function () {
    // 5 of 6 months elapsed; only 500 of 5,000 miles driven.
    $progress = progressFor([
        'interval_months' => 6,
        'interval_miles' => 5000,
        'last_done_at' => Carbon::today()->subDays(152)->toDateString(),
        'last_done_odometer' => 49_500,
    ]);

    expect($progress->axis)->toBe(BindingAxis::Time)
        ->and($progress->label())->toContain('Due in');
});

test('a mileage only interval never binds on time', function () {
    $progress = progressFor([
        'interval_months' => null,
        'interval_miles' => 40_000,
        'last_done_at' => Carbon::today()->subYears(5)->toDateString(),
        'last_done_odometer' => 30_000,
    ]);

    // Five years old, but only 20,000 of 40,000 miles.
    expect($progress->axis)->toBe(BindingAxis::Mileage)
        ->and($progress->status)->toBe(GaugeStatus::Healthy)
        ->and($progress->timeProgress)->toBeNull();
});

test('a time only interval never binds on mileage', function () {
    $progress = progressFor([
        'interval_months' => 12,
        'interval_miles' => null,
        'last_done_at' => Carbon::today()->subDays(200)->toDateString(),
        'last_done_odometer' => 10_000,
    ]);

    expect($progress->axis)->toBe(BindingAxis::Time)
        ->and($progress->mileProgress)->toBeNull();
});

test('an overdue interval reports how far past it is', function () {
    $progress = progressFor([
        'interval_months' => 6,
        'interval_miles' => 5000,
        'last_done_at' => Carbon::today()->subDays(30)->toDateString(),
        'last_done_odometer' => 44_000,
    ]);

    expect($progress->status)->toBe(GaugeStatus::Overdue)
        ->and($progress->milesRemaining)->toBe(-1000)
        ->and($progress->label())->toBe('1,000 mi over')
        ->and($progress->progress)->toBeGreaterThan(1.0)
        ->and($progress->displayProgress())->toBe(1.0);
});

test('the projected odometer drives the gauge, not the last recorded one', function () {
    // Last reading 20 days ago at 48,000, driving 50 mi/day -> ~49,000 today.
    $progress = progressFor(
        [
            'interval_months' => null,
            'interval_miles' => 5000,
            'last_done_at' => Carbon::today()->subDays(60)->toDateString(),
            'last_done_odometer' => 45_000,
        ],
        ['odometer' => 48_000, 'on' => Carbon::today()->subDays(20)->toDateString(), 'rate' => 50],
    );

    // Without projection this would read 2,000 mi left.
    expect($progress->milesRemaining)->toBe(1_000);
});

test('a due interval is exactly at the threshold', function () {
    $progress = progressFor([
        'interval_months' => null,
        'interval_miles' => 5000,
        'last_done_at' => null,
        'last_done_odometer' => 45_000,
    ]);

    expect($progress->status)->toBe(GaugeStatus::Due)
        ->and($progress->progress)->toBe(1.0)
        ->and($progress->label())->toBe('0 mi left');
});
