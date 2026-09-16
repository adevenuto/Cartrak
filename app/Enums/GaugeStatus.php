<?php

namespace App\Enums;

enum GaugeStatus: string
{
    /** No last-done recorded, so there is nothing honest to count from. */
    case Uncalibrated = 'uncalibrated';
    case Healthy = 'healthy';
    case Soon = 'soon';
    case Due = 'due';
    case Overdue = 'overdue';

    /**
     * The three states the interface draws (design doc §6), plus the null one.
     *
     * The enum deliberately keeps five cases. Due and Overdue look identical on
     * screen, but they drive different reminder cadences — SendDueReminders
     * transitions on last_reminded_status and only repeats for Overdue — so
     * collapsing them in the enum would mean rewriting that state machine and
     * migrating stored column values for no product gain.
     */
    public function display(): string
    {
        return match ($this) {
            self::Uncalibrated => 'unknown',
            self::Healthy => 'ok',
            self::Soon => 'due',
            self::Due, self::Overdue => 'overdue',
        };
    }
}
