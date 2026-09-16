<?php

use App\Actions\Events\LogEvent;
use App\Actions\Reminders\SendDueReminders;
use App\Enums\EventType;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use App\Notifications\ServiceDueNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

function dueInterval(Vehicle $vehicle, array $overrides = []): VehicleInterval
{
    return VehicleInterval::factory()->for($vehicle)->for(ServiceType::factory())->create([
        'interval_months' => 6,
        'interval_miles' => null,
        // 7 months ago against a 6 month interval -> overdue.
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
        'last_done_odometer' => null,
        ...$overrides,
    ]);
}

beforeEach(function () {
    Notification::fake();

    $this->user = User::factory()->create();
    $this->vehicle = Vehicle::factory()->for($this->user)->create();
    $this->action = app(SendDueReminders::class);
});

test('an overdue item sends a reminder', function () {
    dueInterval($this->vehicle);

    expect($this->action->handle())->toBe(1);

    Notification::assertSentTo($this->user, ServiceDueNotification::class);
});

test('a healthy item sends nothing', function () {
    dueInterval($this->vehicle, ['last_done_at' => Carbon::today()->toDateString()]);

    expect($this->action->handle())->toBe(0);

    Notification::assertNothingSent();
});

test('an uncalibrated item sends nothing', function () {
    // No baseline at all — a question, not a warning.
    dueInterval($this->vehicle, ['last_done_at' => null]);

    expect($this->action->handle())->toBe(0);

    Notification::assertNothingSent();
});

test('running twice does not send twice', function () {
    dueInterval($this->vehicle);

    expect($this->action->handle())->toBe(1)
        ->and($this->action->handle())->toBe(0)
        ->and($this->action->handle())->toBe(0);

    Notification::assertSentTimes(ServiceDueNotification::class, 1);
});

test('an overdue item is mentioned again after thirty days', function () {
    $interval = dueInterval($this->vehicle);

    $this->action->handle();

    // Still overdue, but a month has passed since we last said so.
    $interval->forceFill(['last_reminded_at' => Carbon::now()->subDays(31)])->save();

    expect($this->action->handle())->toBe(1);

    Notification::assertSentTimes(ServiceDueNotification::class, 2);
});

test('an overdue item is not mentioned again the next day', function () {
    $interval = dueInterval($this->vehicle);

    $this->action->handle();

    $interval->forceFill(['last_reminded_at' => Carbon::now()->subDay()])->save();

    expect($this->action->handle())->toBe(0);
});

test('escalating from soon to due sends again straight away', function () {
    $interval = dueInterval($this->vehicle, [
        // ~5 of 6 months elapsed -> soon.
        'last_done_at' => Carbon::today()->subDays(150)->toDateString(),
    ]);

    expect($this->action->handle())->toBe(1)
        ->and($interval->refresh()->last_reminded_status)->toBe('soon');

    // Time passes and it becomes overdue — that is new information.
    $interval->forceFill([
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
    ])->save();

    expect($this->action->handle())->toBe(1);

    Notification::assertSentTimes(ServiceDueNotification::class, 2);
});

test('logging the service clears the reminder state', function () {
    $type = ServiceType::factory()->create();
    $interval = VehicleInterval::factory()->for($this->vehicle)->for($type)->create([
        'interval_months' => 6,
        'interval_miles' => null,
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
        'last_reminded_status' => 'overdue',
        'last_reminded_at' => Carbon::now(),
    ]);

    app(LogEvent::class)->handle($this->vehicle, [
        'type' => EventType::Visit,
        'odometer' => 60_000,
        'occurred_on' => Carbon::today()->toDateString(),
    ], [['service_type_id' => $type->id]]);

    $interval->refresh();

    // Otherwise the next time this came due we would think we had already said.
    expect($interval->last_reminded_status)->toBeNull()
        ->and($interval->last_reminded_at)->toBeNull();
});

test('another user is never notified about someone else vehicle', function () {
    dueInterval($this->vehicle);
    $stranger = User::factory()->create();

    $this->action->handle();

    Notification::assertNotSentTo($stranger, ServiceDueNotification::class);
});

test('the reminder deep links into the pre-filled log flow', function () {
    dueInterval($this->vehicle);

    $this->action->handle();

    Notification::assertSentTo($this->user, ServiceDueNotification::class, function ($notification) {
        $payload = $notification->toArray($this->user);

        // The brief's loop is "Oil due -> tap -> Save", so the link has to open
        // the log sheet, not just the vehicle page.
        return str_contains($payload['url'], '?log=visit');
    });
});
