<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The in-app inbox: everything we have told this user, in one place.
 *
 * The email is the nudge; this is the record. Without it a reminder that
 * arrives while you are busy is gone, and there is nothing to check later.
 */
class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate(30)
            ->through(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toIso8601String(),
                ...$this->payload($notification),
            ]);

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function update(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()
            ->whereKey($notification)
            ->firstOrFail()
            ->markAsRead();

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'kind' => is_string($data['type'] ?? null) ? $data['type'] : 'unknown',
            'vehicle_name' => is_string($data['vehicle_name'] ?? null) ? $data['vehicle_name'] : null,
            'title' => is_string($data['service'] ?? null)
                ? $data['service']
                : (is_string($data['component'] ?? null) ? $data['component'] : 'Update'),
            'detail' => is_string($data['detail'] ?? null) ? $data['detail'] : null,
            'url' => is_string($data['url'] ?? null) ? $data['url'] : null,
        ];
    }
}
