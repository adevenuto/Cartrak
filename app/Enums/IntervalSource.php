<?php

namespace App\Enums;

/**
 * Where a vehicle interval's effective values came from. Phase 1 only ever
 * writes Default and UserOverride; Vin arrives with CarMD/VIN schedules later.
 */
enum IntervalSource: string
{
    case Default = 'default';
    case Vin = 'vin';
    case UserOverride = 'user_override';
}
