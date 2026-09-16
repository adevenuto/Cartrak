<?php

namespace App\Notifications;

use App\Enums\GaugeStatus;
use App\Models\Vehicle;
use App\Support\IntervalProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Your oil change is due" — by email and into the in-app inbox.
 *
 * The call to action deep-links into the pre-filled log flow rather than the
 * vehicle page, because the brief's whole reminder loop is "Oil due -> tap ->
 * Save". Landing someone on a page where they still have to find the right
 * button loses most of the value.
 */
class ServiceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Vehicle $vehicle,
        private string $serviceName,
        private GaugeStatus $status,
        private string $detail,
    ) {}

    /**
     * Built from a computed gauge so the wording always matches what the app
     * shows — including which axis is binding.
     */
    public static function from(Vehicle $vehicle, IntervalProgress $gauge): self
    {
        return new self(
            $vehicle,
            $gauge->interval->serviceType->name,
            $gauge->status,
            $gauge->label(),
        );
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $heading = $this->status === GaugeStatus::Overdue
            ? "{$this->serviceName} is overdue"
            : "{$this->serviceName} is due";

        return (new MailMessage)
            ->subject("{$this->vehicle->displayName()}: {$heading}")
            ->greeting($heading)
            ->line("{$this->vehicle->displayName()} — {$this->detail}.")
            ->action('Log it now', $this->logUrl())
            ->line('Logging it resets the gauge and starts the next interval.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_due',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->displayName(),
            'service' => $this->serviceName,
            'status' => $this->status->value,
            'detail' => $this->detail,
            'url' => $this->logUrl(),
        ];
    }

    /**
     * Deep link: the vehicle page with the quick-add sheet already open on the
     * service lane, so the reminder is two taps from done.
     */
    private function logUrl(): string
    {
        return route('vehicles.show', $this->vehicle).'?log=visit';
    }
}
