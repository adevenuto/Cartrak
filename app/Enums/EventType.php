<?php

namespace App\Enums;

/**
 * The four things a user can log. Every one of them carries an odometer reading
 * and a date — that is the whole spine. Visits additionally advance maintenance
 * intervals; the rest are purely financial or purely a reading.
 */
enum EventType: string
{
    case Visit = 'visit';
    case Fuel = 'fuel';
    case Expense = 'expense';
    case Odometer = 'odometer';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Visit => 'Service',
            self::Fuel => 'Fuel',
            self::Expense => 'Expense',
            self::Odometer => 'Miles',
        };
    }
}
