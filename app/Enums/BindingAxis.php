<?php

namespace App\Enums;

/**
 * Which of the two axes is driving a gauge — the "whichever comes first" the
 * whole product is built around.
 */
enum BindingAxis: string
{
    case Time = 'time';
    case Mileage = 'mileage';
    case None = 'none';
}
