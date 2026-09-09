<?php

namespace App\Support;

/**
 * Costs are stored as integer cents everywhere. Users type dollars, so the
 * conversion happens once, at the boundary, in one place.
 */
class Money
{
    /**
     * Dollars from a form field to integer cents. Rounds rather than truncates,
     * so 10.005 becomes 1001 and not 1000.
     */
    public static function toCents(mixed $dollars): ?int
    {
        if ($dollars === null || $dollars === '') {
            return null;
        }

        if (! is_numeric($dollars)) {
            return null;
        }

        return (int) round((float) $dollars * 100);
    }

    /**
     * Integer cents back to a dollar amount for display or form repopulation.
     */
    public static function toDollars(?int $cents): ?float
    {
        return $cents === null ? null : round($cents / 100, 2);
    }
}
