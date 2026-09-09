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
}
