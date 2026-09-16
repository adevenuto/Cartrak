<?php

namespace App\Actions\Reminders;

use App\Enums\GaugeStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\ServiceDueNotification;
use App\Support\IntervalProgress;
use App\Support\VehicleGauges;
use Illuminate\Support\Facades\Date;

/**
 * Decides who gets told what, and — more importantly — who does not.
 *
 * The cadence is: once when something crosses into "soon", once when it becomes
 * due, then at most monthly while it stays overdue. State lives on the interval
 * row, so re-running this (a retry, a manual run, a second scheduler tick) can
 * never re-send the same reminder.
 *
 * That restraint is the whole design. A reminder arriving every morning about a
 * thing you have consciously deferred is how an app gets muted, and a muted app
 * cannot tell you about the brake job that actually matters.
 */
class SendDueReminders
{
    /** Days before an unchanged overdue item is mentioned again. */
    private const REPEAT_AFTER_DAYS = 30;

    /**
     * @return int the number of reminders dispatched
     */
    public function handle(): int
    {
        $sent = 0;

        User::query()
            ->whereHas('vehicles')
            ->with('vehicles.intervals.serviceType')
            ->chunkById(50, function ($users) use (&$sent): void {
                foreach ($users as $user) {
                    $sent += $this->forUser($user);
                }
            });

        return $sent;
    }

    private function forUser(User $user): int
    {
        $sent = 0;

        foreach ($user->vehicles as $vehicle) {
            $sent += $this->forVehicle($user, $vehicle);
        }

        return $sent;
    }

    private function forVehicle(User $user, Vehicle $vehicle): int
    {
        $sent = 0;

        foreach (VehicleGauges::for($vehicle)->gauges as $gauge) {
            if (! $this->shouldSend($gauge)) {
                continue;
            }

            $user->notify(ServiceDueNotification::from($vehicle, $gauge));
            $this->recordSend($gauge);
            $sent++;
        }

        return $sent;
    }

    private function shouldSend(IntervalProgress $gauge): bool
    {
        // Nothing to say about an item that is fine, or one we have no baseline
        // for — an uncalibrated gauge is a question, not a warning.
        if (! in_array($gauge->status, [GaugeStatus::Soon, GaugeStatus::Due, GaugeStatus::Overdue], true)) {
            return false;
        }

        $interval = $gauge->interval;

        // Never mentioned, or it has moved on to a more serious state.
        if ($interval->last_reminded_status !== $gauge->status->value) {
            return true;
        }

        // Same state as last time: only overdue items are worth repeating, and
        // only monthly.
        if ($gauge->status !== GaugeStatus::Overdue) {
            return false;
        }

        return $interval->last_reminded_at === null
            || $interval->last_reminded_at->lessThanOrEqualTo(
                Date::now()->subDays(self::REPEAT_AFTER_DAYS),
            );
    }

    private function recordSend(IntervalProgress $gauge): void
    {
        $gauge->interval->forceFill([
            'last_reminded_status' => $gauge->status->value,
            'last_reminded_at' => Date::now(),
        ])->save();
    }
}
