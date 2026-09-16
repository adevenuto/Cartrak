<?php

namespace App\Enums;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

/**
 * The coarse "roughly when was this last done?" answers offered during quick
 * calibrate.
 *
 * Deliberately coarse: nobody remembers the date of their last oil change, and
 * asking for one would stall the flow the brief wants finished in seconds. Each
 * bucket resolves to its midpoint, which is close enough for a gauge that is
 * itself an estimate — and the user can set an exact date later.
 */
enum LastDoneEstimate: string
{
    case ThisMonth = 'this_month';
    case OneToThree = '1_3';
    case ThreeToSix = '3_6';
    case SixToTwelve = '6_12';
    case OverAYear = 'over_year';
    case NotSure = 'not_sure';

    public function label(): string
    {
        return match ($this) {
            self::ThisMonth => 'This month',
            self::OneToThree => '1–3 months ago',
            self::ThreeToSix => '3–6 months ago',
            self::SixToTwelve => '6–12 months ago',
            self::OverAYear => 'Over a year ago',
            self::NotSure => 'Not sure',
        };
    }

    /**
     * The midpoint of the bucket. Null means "leave it uncalibrated" — an
     * honest unknown beats a fabricated date.
     */
    public function toDate(): ?CarbonInterface
    {
        $monthsAgo = match ($this) {
            self::ThisMonth => 0,
            self::OneToThree => 2,
            self::ThreeToSix => 4,
            self::SixToTwelve => 9,
            self::OverAYear => 18,
            self::NotSure => null,
        };

        return $monthsAgo === null ? null : Date::today()->subMonths($monthsAgo);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
