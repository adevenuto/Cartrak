<?php

namespace App\Http\Controllers;

use App\Actions\Events\LogEvent;
use App\Http\Requests\Events\StoreEventRequest;
use App\Models\Event;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * One endpoint for all four quick-add lanes; the request object decides
     * what a given lane contributes and LogEvent keeps the derived state right.
     */
    public function store(StoreEventRequest $request, Vehicle $vehicle, LogEvent $logEvent): RedirectResponse
    {
        Gate::authorize('update', $vehicle);

        $logEvent->handle($vehicle, $request->eventAttributes(), $request->lineItems());

        return back();
    }

    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('update', $event->vehicle);

        $event->delete();

        return back();
    }
}
