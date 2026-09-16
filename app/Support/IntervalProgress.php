<?php

namespace App\Support;

use App\Enums\BindingAxis;
use App\Enums\GaugeStatus;
use App\Models\VehicleInterval;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

/**
 * One gauge: how far through a service interval a vehicle is, on both axes at
 * once, and which of them binds.
 *
 * This is the "whichever comes first" rule the product is built around. An
 * interval may carry a time axis, a mileage axis, or both; progress is the
 * larger of the two, and the binding axis is whichever produced it. Brake pads
 * are mileage-only, registration is time-only, an oil change is both.
 *
 * An interval with no last-done is deliberately NOT treated as due. We simply
 * do not know when it was last done, and guessing would tell someone their oil
 * is overdue when they changed it last week.
 */
readonly class IntervalProgress
{
    /** Average days in a month, so 6 months is a real span rather than 180. */
    /**
     * The average Gregorian month. Public because the header's "avg / month"
     * cell converts a per-day rate with it, and two copies of this number would
     * drift the moment one was tuned.
     */
    public const DAYS_PER_MONTH = 30.44;

    /** Past roughly two months, a day count stops being readable. */
    private const MONTH_SWITCH_DAYS = 60;

    private function __construct(
        public VehicleInterval $interval,
        public GaugeStatus $status,
        public BindingAxis $axis,
        public float $progress,
        public ?int $milesRemaining,
        public ?int $daysRemaining,
        public ?float $timeProgress,
        public ?float $mileProgress,
    ) {}

    public static function for(
        VehicleInterval $interval,
        VehicleMileage $mileage,
        ?CarbonInterface $asOf = null,
    ): self {
        $asOf = $asOf ?? Date::today();

        if ($interval->last_done_at === null && $interval->last_done_odometer === null) {
            return new self(
                $interval, GaugeStatus::Uncalibrated, BindingAxis::None,
                0.0, null, null, null, null,
            );
        }

        [$timeProgress, $daysRemaining] = self::timeAxis($interval, $asOf);
        [$mileProgress, $milesRemaining] = self::mileageAxis($interval, $mileage);

        $progress = max($timeProgress ?? 0.0, $mileProgress ?? 0.0);

        // The binding axis is whichever got furthest. Ties go to time, because
        // "due in 0 days" is a clearer thing to read than "0 mi left".
        $axis = match (true) {
            $timeProgress === null && $mileProgress === null => BindingAxis::None,
            $mileProgress === null => BindingAxis::Time,
            $timeProgress === null => BindingAxis::Mileage,
            $timeProgress >= $mileProgress => BindingAxis::Time,
            default => BindingAxis::Mileage,
        };

        return new self(
            interval: $interval,
            status: self::statusFor($axis, $progress),
            axis: $axis,
            progress: round($progress, 4),
            milesRemaining: $milesRemaining,
            daysRemaining: $daysRemaining,
            timeProgress: $timeProgress === null ? null : round($timeProgress, 4),
            mileProgress: $mileProgress === null ? null : round($mileProgress, 4),
        );
    }

    /**
     * Progress capped for display. The real value can exceed 1 by a long way on
     * something years overdue; a ring cannot draw that.
     */
    public function displayProgress(): float
    {
        return min(1.0, max(0.0, $this->progress));
    }

    /**
     * What the gauge says underneath its ring — always the binding axis, since
     * that is the number that will actually run out first.
     */
    public function label(): string
    {
        return match ($this->status) {
            GaugeStatus::Uncalibrated => 'Not set',
            default => match ($this->axis) {
                BindingAxis::Mileage => $this->mileageLabel(),
                BindingAxis::Time => $this->timeLabel(),
                BindingAxis::None => 'No interval set',
            },
        };
    }

    private function mileageLabel(): string
    {
        $miles = $this->milesRemaining ?? 0;

        return $miles < 0
            ? 'Overdue by '.number_format(abs($miles)).' mi'
            : number_format($miles).' mi to go';
    }

    /**
     * Days are only legible for about two months. Past that the design's
     * vocabulary switches to months, because "Due in 240 days" is a number
     * nobody converts in their head.
     */
    private function timeLabel(): string
    {
        $days = $this->daysRemaining ?? 0;

        if ($days < 0) {
            return 'Overdue by '.self::humanSpan(abs($days));
        }

        return $days === 0
            ? 'Due today'
            : self::humanSpan($days).' to go';
    }

    private static function humanSpan(int $days): string
    {
        if ($days < self::MONTH_SWITCH_DAYS) {
            return $days.' '.str('day')->plural($days);
        }

        $months = $days / self::DAYS_PER_MONTH;

        // One decimal below a year, whole months above it: "1.4 mo" is useful,
        // "18.3 mo" is just noise.
        return ($months < 12 ? round($months, 1) : round($months)).' mo';
    }

    /**
     * @return array{0: float|null, 1: int|null}
     */
    private static function timeAxis(VehicleInterval $interval, CarbonInterface $asOf): array
    {
        if ($interval->interval_months === null || $interval->last_done_at === null) {
            return [null, null];
        }

        $span = $interval->interval_months * self::DAYS_PER_MONTH;
        $elapsed = (int) $interval->last_done_at->startOfDay()->diffInDays($asOf->startOfDay());

        return [$elapsed / $span, (int) round($span) - $elapsed];
    }

    /**
     * @return array{0: float|null, 1: int|null}
     */
    private static function mileageAxis(VehicleInterval $interval, VehicleMileage $mileage): array
    {
        if ($interval->interval_miles === null || $interval->last_done_odometer === null) {
            return [null, null];
        }

        $driven = $mileage->projectedOdometer - $interval->last_done_odometer;

        return [
            $driven / $interval->interval_miles,
            $interval->interval_miles - $driven,
        ];
    }

    private static function statusFor(BindingAxis $axis, float $progress): GaugeStatus
    {
        if ($axis === BindingAxis::None) {
            return GaugeStatus::Uncalibrated;
        }

        return match (true) {
            $progress > Config::float('vehicles.gauges.due') => GaugeStatus::Overdue,
            $progress >= Config::float('vehicles.gauges.due') => GaugeStatus::Due,
            $progress >= Config::float('vehicles.gauges.soon') => GaugeStatus::Soon,
            default => GaugeStatus::Healthy,
        };
    }
}
