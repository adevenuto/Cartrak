<?php

namespace App\Support;

use App\Enums\BindingAxis;
use App\Enums\GaugeStatus;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use Illuminate\Support\Collection;

/**
 * A vehicle's whole gauge cluster, plus the one thing that needs attention
 * soonest.
 *
 * The garage card shows the worst item rather than an averaged score: nine
 * healthy items must never dilute one overdue brake job, and "does this car
 * need anything?" is the question a garage grid exists to answer.
 */
readonly class VehicleGauges
{
    /**
     * @param  Collection<int, IntervalProgress>  $gauges
     */
    private function __construct(
        public VehicleMileage $mileage,
        public Collection $gauges,
    ) {}

    public static function for(Vehicle $vehicle): self
    {
        $mileage = VehicleMileage::for($vehicle);

        $intervals = $vehicle->relationLoaded('intervals')
            ? $vehicle->intervals
            : $vehicle->intervals()->with('serviceType')->get();

        $gauges = $intervals
            ->filter(fn (VehicleInterval $interval): bool => $interval->is_active)
            ->map(fn (VehicleInterval $interval): IntervalProgress => IntervalProgress::for($interval, $mileage))
            ->values();

        return new self($mileage, $gauges);
    }

    /**
     * Whatever is closest to due. Uncalibrated items are excluded — we cannot
     * rank something we have no baseline for.
     */
    public function worst(): ?IntervalProgress
    {
        return $this->gauges
            ->reject(fn (IntervalProgress $g): bool => $g->status === GaugeStatus::Uncalibrated)
            ->sortByDesc(fn (IntervalProgress $g): float => $g->progress)
            ->first();
    }

    /**
     * @return Collection<int, IntervalProgress>
     */
    public function needingAttention(): Collection
    {
        return $this->gauges->filter(fn (IntervalProgress $g): bool => in_array(
            $g->status,
            [GaugeStatus::Due, GaugeStatus::Overdue],
            true,
        ))->values();
    }

    public function uncalibratedCount(): int
    {
        return $this->gauges
            ->filter(fn (IntervalProgress $g): bool => $g->status === GaugeStatus::Uncalibrated)
            ->count();
    }

    /**
     * Sorted for display: the most urgent first, uncalibrated items last, since
     * they are a prompt to act rather than a countdown.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->gauges
            ->sortBy([
                fn (IntervalProgress $a, IntervalProgress $b) => ($a->status === GaugeStatus::Uncalibrated ? 1 : 0)
                    <=> ($b->status === GaugeStatus::Uncalibrated ? 1 : 0),
                fn (IntervalProgress $a, IntervalProgress $b) => $b->progress <=> $a->progress,
            ])
            ->map(fn (IntervalProgress $g): array => self::gaugeToArray($g))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function gaugeToArray(IntervalProgress $gauge): array
    {
        return [
            'id' => $gauge->interval->id,
            'service_type_id' => $gauge->interval->service_type_id,
            'name' => $gauge->interval->serviceType->name,
            'status' => $gauge->status->value,
            'axis' => $gauge->axis->value,
            'progress' => $gauge->displayProgress(),
            'raw_progress' => $gauge->progress,
            // Uncapped on purpose: 112% is a real reading and must print as 112.
            'percent' => (int) round($gauge->progress * 100),
            'basis' => self::basisFor($gauge),
            'label' => $gauge->label(),
            'miles_remaining' => $gauge->milesRemaining,
            'days_remaining' => $gauge->daysRemaining,
            'interval_months' => $gauge->interval->interval_months,
            'interval_miles' => $gauge->interval->interval_miles,
            'last_done_at' => $gauge->interval->last_done_at?->toDateString(),
            'last_done_odometer' => $gauge->interval->last_done_odometer,
            'source' => $gauge->interval->source->value,
        ];
    }

    /**
     * What the gauge is measured against, as the card eyebrow shows it:
     * "5,000 mi" or "12 mo".
     *
     * Reads the axis that actually binds, so a gauge limited by time does not
     * advertise a mileage interval it will never reach first.
     */
    private static function basisFor(IntervalProgress $gauge): ?string
    {
        $interval = $gauge->interval;

        $miles = $interval->interval_miles === null
            ? null
            : number_format($interval->interval_miles).' mi';

        $months = $interval->interval_months === null
            ? null
            : $interval->interval_months.' mo';

        return match ($gauge->axis) {
            BindingAxis::Mileage => $miles,
            BindingAxis::Time => $months,
            // Uncalibrated: nothing binds yet, so show whatever is configured.
            BindingAxis::None => $miles ?? $months,
        };
    }

    /**
     * Real computed MPG against the EPA sticker.
     *
     * The average of full-tank fill-ups we actually measured, compared with the
     * published figure. Deliberately not shown until there are a few readings:
     * a single fill-up says more about the pump's cut-off than the car.
     *
     * @return array<string, mixed>|null
     */
    public static function fuelBenchmark(Vehicle $vehicle): ?array
    {
        $sticker = $vehicle->epa_mpg_combined;

        $readings = $vehicle->events()
            ->whereNotNull('mpg')
            ->orderByDesc('occurred_on')
            ->limit(10)
            ->pluck('mpg')
            ->map(fn (mixed $mpg): float => (float) $mpg)
            ->filter(fn (float $mpg): bool => $mpg > 0);

        if ($readings->count() < 3 && $sticker === null) {
            return null;
        }

        $actual = $readings->count() >= 3
            ? round($readings->avg(), 1)
            : null;

        return [
            'actual' => $actual,
            'sticker' => $sticker,
            'city' => $vehicle->epa_mpg_city,
            'highway' => $vehicle->epa_mpg_highway,
            'reading_count' => $readings->count(),
            // Percent difference from the sticker, positive means better.
            'delta_percent' => $actual !== null && $sticker !== null && $sticker > 0
                ? round((($actual - $sticker) / $sticker) * 100)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mileageToArray(): array
    {
        return [
            'last_odometer' => $this->mileage->lastOdometer,
            'last_odometer_at' => $this->mileage->lastOdometerAt?->toDateString(),
            'projected_odometer' => $this->mileage->projectedOdometer,
            'miles_per_day' => $this->mileage->milesPerDay,
            'days_since_reading' => $this->mileage->daysSinceReading,
            'is_projected' => $this->mileage->isProjected(),
            'needs_reading' => $this->mileage->needsReading,
        ];
    }
}
