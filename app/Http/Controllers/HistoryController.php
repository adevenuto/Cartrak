<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    /**
     * Every event the user has logged, newest first, across all their vehicles.
     */
    public function __invoke(Request $request): Response
    {
        $events = Event::query()
            ->whereHas('vehicle', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['vehicle', 'lineItems.serviceType'])
            ->latestFirst()
            ->paginate(30)
            ->through(fn (Event $event): array => [
                'id' => $event->id,
                'vehicle_id' => $event->vehicle_id,
                'vehicle_name' => $event->vehicle->displayName(),
                'type' => $event->type->value,
                'type_label' => $event->type->label(),
                'odometer' => $event->odometer,
                'occurred_on' => $event->occurred_on->toDateString(),
                'cost_cents' => $event->cost_cents,
                'notes' => $event->notes,
                'location' => $event->location,
                'gallons' => $event->gallons === null ? null : (float) $event->gallons,
                'mpg' => $event->mpg === null ? null : (float) $event->mpg,
                'category' => $event->category,
                'line_items' => $event->lineItems->map(fn ($item): array => [
                    'id' => $item->id,
                    'name' => $item->serviceType->name,
                    'cost_cents' => $item->cost_cents,
                ])->all(),
            ]);

        return Inertia::render('History', [
            'events' => $events,
        ]);
    }
}
