<?php

use App\Actions\Reminders\SendDueReminders;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function overdueInterval(Vehicle $vehicle): VehicleInterval
{
    return VehicleInterval::factory()->for($vehicle)->for(ServiceType::factory())->create([
        'interval_months' => 6,
        'interval_miles' => null,
        'last_done_at' => Carbon::today()->subMonths(7)->toDateString(),
    ]);
}

test('a due reminder lands in the in-app inbox', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create();
    overdueInterval($vehicle);

    // Not faked: this exercises the real mail + database channels.
    app(SendDueReminders::class)->handle();

    expect($this->user->notifications()->count())->toBe(1)
        ->and($this->user->unreadNotifications()->count())->toBe(1);

    $this->get(route('notifications'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Notifications')
            ->where('unreadCount', 1)
            ->has('notifications.data', 1)
            ->where('notifications.data.0.kind', 'service_due'));
});

test('the shell shares an unread count for the badge', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create();
    overdueInterval($vehicle);

    app(SendDueReminders::class)->handle();

    $this->get(route('garage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('unreadNotifications', 1));
});

test('a notification can be marked read', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create();
    overdueInterval($vehicle);
    app(SendDueReminders::class)->handle();

    $notification = $this->user->notifications()->firstOrFail();

    $this->patch(route('notifications.update', $notification->id))
        ->assertSessionHasNoErrors();

    expect($this->user->unreadNotifications()->count())->toBe(0);
});

test('everything can be marked read at once', function () {
    $vehicle = Vehicle::factory()->for($this->user)->create();
    overdueInterval($vehicle);
    overdueInterval($vehicle);
    app(SendDueReminders::class)->handle();

    expect($this->user->unreadNotifications()->count())->toBe(2);

    $this->patch(route('notifications.read-all'));

    expect($this->user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('a user cannot read another user notification', function () {
    $stranger = User::factory()->create();
    $vehicle = Vehicle::factory()->for($stranger)->create();
    overdueInterval($vehicle);
    app(SendDueReminders::class)->handle();

    $theirs = $stranger->notifications()->firstOrFail();

    $this->patch(route('notifications.update', $theirs->id))->assertNotFound();

    expect($stranger->fresh()->unreadNotifications()->count())->toBe(1);
});

test('the garage summary counts what is due across every vehicle', function () {
    $first = Vehicle::factory()->for($this->user)->create();
    $second = Vehicle::factory()->for($this->user)->create();
    overdueInterval($first);
    overdueInterval($second);

    // Someone else's overdue car must never reach this count.
    overdueInterval(Vehicle::factory()->create());

    $this->get(route('garage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('garage.due_count', 2));
});
