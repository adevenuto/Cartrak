<?php

namespace App\Http\Controllers;

use App\Actions\Vehicles\SeedVehicleIntervals;
use App\Enums\IntervalSource;
use App\Enums\LastDoneEstimate;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Calibrating and overriding a vehicle's schedule.
 *
 * Two entry points onto the same rows: the quick-calibrate flow right after a
 * car is added, and per-item edits from the vehicle's gauge cluster.
 */
class VehicleIntervalController extends Controller
{
    /** The items worth asking about up front, in order. */
    private const CALIBRATE_KEYS = [
        'oil-change', 'tire-rotation', 'brake-pads', 'registration',
    ];

    /**
     * Quick calibrate: "roughly when did you last do these?" so the gauges
     * start from something real instead of all grey.
     */
    /**
     * @return array<string, int>
     */
    private static function calibrateOrder(): array
    {
        return array_flip(self::CALIBRATE_KEYS);
    }

    public function create(Vehicle $vehicle, SeedVehicleIntervals $seed): Response
    {
        Gate::authorize('update', $vehicle);

        // A vehicle added before schedules existed has no interval rows, which
        // would make this screen empty and the flow a dead end. Seeding is
        // idempotent, so this is safe to run every time.
        if ($vehicle->intervals()->doesntExist()) {
            $seed->handle($vehicle);
        }

        $intervals = $vehicle->intervals()
            ->with('serviceType')
            ->whereHas('serviceType', fn ($query) => $query->whereIn('key', self::CALIBRATE_KEYS))
            ->get()
            ->sortBy(fn (VehicleInterval $i): int => self::calibrateOrder()[$i->serviceType->key] ?? PHP_INT_MAX)
            ->values()
            ->map(fn (VehicleInterval $i): array => [
                'id' => $i->id,
                'name' => $i->serviceType->name,
                'interval_months' => $i->interval_months,
                'interval_miles' => $i->interval_miles,
                'last_done_at' => $i->last_done_at?->toDateString(),
                'last_done_odometer' => $i->last_done_odometer,
            ]);

        return Inertia::render('vehicles/Calibrate', [
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->displayName(),
                'last_odometer' => $vehicle->last_odometer,
            ],
            'intervals' => $intervals,
            'estimates' => LastDoneEstimate::options(),
        ]);
    }

    /**
     * Save the quick-calibrate answers in one go. "Not sure" leaves an item
     * uncalibrated rather than inventing a date for it.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        Gate::authorize('update', $vehicle);

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*.interval_id' => ['required', 'integer'],
            'answers.*.estimate' => ['required', 'string'],
            'answers.*.odometer' => ['nullable', 'integer', 'min:0', 'max:2000000'],
        ]);

        $intervals = $vehicle->intervals()->get()->keyBy('id');

        foreach ($validated['answers'] ?? [] as $answer) {
            $interval = $intervals->get($answer['interval_id']);
            $estimate = LastDoneEstimate::tryFrom($answer['estimate']);

            if ($interval === null || $estimate === null) {
                continue;
            }

            $date = $estimate->toDate();

            if ($date === null) {
                continue;
            }

            $interval->forceFill([
                'last_done_at' => $date->toDateString(),
                // Without an odometer the mileage axis has no baseline, so it
                // simply does not participate until a service is logged.
                'last_done_odometer' => $answer['odometer'] ?? null,
            ])->save();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Gauges calibrated.')]);

        return to_route('vehicles.show', $vehicle);
    }

    /**
     * Per-item edit from the gauge cluster: last-done, and the interval itself.
     */
    public function update(Request $request, VehicleInterval $interval): RedirectResponse
    {
        Gate::authorize('update', $interval->vehicle);

        $validated = $request->validate([
            'last_done_at' => ['nullable', 'date', 'before_or_equal:today'],
            'last_done_odometer' => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'interval_months' => ['nullable', 'integer', 'min:1', 'max:240'],
            'interval_miles' => ['nullable', 'integer', 'min:100', 'max:200000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $changedInterval = array_key_exists('interval_months', $validated)
            || array_key_exists('interval_miles', $validated);

        $interval->fill($validated);

        // Changing either axis makes this the user's schedule, not ours — which
        // is what stops a later default/VIN refresh from overwriting it.
        if ($changedInterval && $interval->isDirty(['interval_months', 'interval_miles'])) {
            $interval->source = IntervalSource::UserOverride;
        }

        $interval->save();

        return back();
    }
}
