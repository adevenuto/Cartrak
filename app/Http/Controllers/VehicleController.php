<?php

namespace App\Http\Controllers;

use App\Actions\Events\LogEvent;
use App\Actions\Vehicles\SeedVehicleIntervals;
use App\Actions\Vehicles\SyncVehiclePhotos;
use App\Enums\EventType;
use App\Http\Requests\Vehicles\StoreVehicleRequest;
use App\Http\Requests\Vehicles\UpdateVehicleRequest;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Support\VehicleGauges;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    /**
     * The garage: one card per vehicle. Phase 2 adds the health ring.
     */
    public function index(Request $request): Response
    {
        $vehicles = $request->user()->vehicles()
            ->with(['primaryPhoto', 'intervals.serviceType'])
            ->withCount('events')
            ->orderBy('created_at')
            ->get()
            ->map(function (Vehicle $vehicle): array {
                $gauges = VehicleGauges::for($vehicle);
                $worst = $gauges->worst();

                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->displayName(),
                    'year' => $vehicle->year,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'trim' => $vehicle->trim,
                    'color' => $vehicle->color,
                    'photo_thumb_url' => $vehicle->primaryPhoto
                        ? route('vehicle-photos.thumbnail', $vehicle->primaryPhoto)
                        : null,
                    'photo_color' => $vehicle->primaryPhoto?->placeholder_color,
                    'last_odometer' => $vehicle->last_odometer,
                    'last_odometer_at' => $vehicle->last_odometer_at?->toDateString(),
                    'events_count' => $vehicle->events_count,
                    // One ring per card: whatever is closest to due, named. An
                    // averaged score would let nine healthy items hide one overdue
                    // brake job.
                    'worst' => $worst === null ? null : VehicleGauges::gaugeToArray($worst),
                    'due_count' => $gauges->needingAttention()->count(),
                    'uncalibrated_count' => $gauges->uncalibratedCount(),
                    'mileage' => $gauges->mileageToArray(),
                ];
            });

        return Inertia::render('Garage', [
            'vehicles' => $vehicles,
            // The garage FAB opens the quick-add sheet in place, so the sheet's
            // options travel with the page rather than costing a second request.
            'serviceTypes' => ServiceType::orderBy('sort_order')->get(['id', 'name', 'category']),
            'expenseCategories' => config('vehicles.expense_categories'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vehicles/Create', [
            'makes' => config('vehicles.makes'),
            'colors' => config('vehicles.colors'),
            'minYear' => config('vehicles.min_year'),
        ]);
    }

    /**
     * Creating a vehicle also writes its first event. The odometer the user
     * gives at setup is a reading like any other, so it enters through the
     * spine rather than being set directly on the vehicle.
     */
    public function store(
        StoreVehicleRequest $request,
        LogEvent $logEvent,
        SyncVehiclePhotos $syncPhotos,
        SeedVehicleIntervals $seedIntervals,
    ): RedirectResponse {
        $data = $request->validated();

        $vehicle = $request->user()->vehicles()->create(
            collect($data)->except([
                'odometer', 'photos', 'photo_order', 'removed_photo_ids',
            ])->all(),
        );

        $seedIntervals->handle($vehicle);
        $syncPhotos->handle($vehicle, $request->photos(), $request->photoOrder());

        $logEvent->handle($vehicle, [
            'type' => EventType::Odometer,
            'odometer' => $data['odometer'],
            'occurred_on' => now()->toDateString(),
            'notes' => 'Starting reading',
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Vehicle added.')]);

        // Straight into quick calibrate — the brief's "setup is the wow". It is
        // skippable, and skipping lands on the vehicle either way.
        return to_route('vehicles.calibrate', $vehicle);
    }

    public function show(Request $request, Vehicle $vehicle): Response
    {
        Gate::authorize('view', $vehicle);

        $vehicle->load(['primaryPhoto', 'intervals.serviceType']);

        $gauges = VehicleGauges::for($vehicle);

        $events = $vehicle->events()
            ->with('lineItems.serviceType')
            ->latestFirst()
            ->get()
            ->map(fn ($event): array => [
                'id' => $event->id,
                'type' => $event->type->value,
                'type_label' => $event->type->label(),
                'odometer' => $event->odometer,
                'occurred_on' => $event->occurred_on->toDateString(),
                'cost_cents' => $event->cost_cents,
                'notes' => $event->notes,
                'location' => $event->location,
                'gallons' => $event->gallons === null ? null : (float) $event->gallons,
                'full_tank' => $event->full_tank,
                'mpg' => $event->mpg === null ? null : (float) $event->mpg,
                'category' => $event->category,
                'line_items' => $event->lineItems->map(fn ($item): array => [
                    'id' => $item->id,
                    'name' => $item->serviceType->name,
                    'cost_cents' => $item->cost_cents,
                ])->all(),
            ]);

        return Inertia::render('vehicles/Show', [
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->displayName(),
                'nickname' => $vehicle->nickname,
                'vin' => $vehicle->vin,
                'year' => $vehicle->year,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'trim' => $vehicle->trim,
                'engine' => $vehicle->engine,
                'color' => $vehicle->color,
                'photo_url' => $vehicle->primaryPhoto
                    ? route('vehicle-photos.show', $vehicle->primaryPhoto)
                    : null,
                'photo_color' => $vehicle->primaryPhoto?->placeholder_color,
                'last_odometer' => $vehicle->last_odometer,
                'last_odometer_at' => $vehicle->last_odometer_at?->toDateString(),
            ],
            'events' => $events,
            'gauges' => $gauges->toArray(),
            'mileage' => $gauges->mileageToArray(),
            'serviceTypes' => ServiceType::orderBy('sort_order')->get(['id', 'name', 'category']),
            'expenseCategories' => config('vehicles.expense_categories'),
        ]);
    }

    public function edit(Vehicle $vehicle): Response
    {
        Gate::authorize('update', $vehicle);

        return Inertia::render('vehicles/Edit', [
            'vehicle' => [
                ...$vehicle->only([
                    'id', 'nickname', 'vin', 'year', 'make', 'model', 'trim', 'engine', 'color',
                ]),
                'photos' => $vehicle->photos()->get()->map(fn (VehiclePhoto $photo): array => [
                    'id' => $photo->id,
                    'url' => route('vehicle-photos.thumbnail', $photo),
                    'color' => $photo->placeholder_color,
                ])->all(),
            ],
            'makes' => config('vehicles.makes'),
            'colors' => config('vehicles.colors'),
            'minYear' => config('vehicles.min_year'),
        ]);
    }

    public function update(
        UpdateVehicleRequest $request,
        Vehicle $vehicle,
        SyncVehiclePhotos $syncPhotos,
    ): RedirectResponse {
        Gate::authorize('update', $vehicle);

        $vehicle->update(
            collect($request->validated())->except([
                'photos', 'photo_order', 'removed_photo_ids',
            ])->all(),
        );

        $syncPhotos->handle(
            $vehicle,
            $request->photos(),
            $request->photoOrder(),
            $request->removedPhotoIds(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Vehicle updated.')]);

        return to_route('vehicles.show', $vehicle);
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        Gate::authorize('delete', $vehicle);

        $vehicle->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Vehicle removed.')]);

        return to_route('garage');
    }
}
