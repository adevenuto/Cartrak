<?php

namespace App\Support;

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
