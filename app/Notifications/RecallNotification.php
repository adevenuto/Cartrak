<?php

namespace App\Notifications;

use App\Models\Recall;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A safety recall NHTSA has published for this vehicle.
 *
 * Kept free forever per the brief — it is the best word-of-mouth hook the
 * product has, and paywalling a safety notice would be indefensible anyway.
 */
class RecallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Vehicle $vehicle,
        private Recall $recall,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Safety recall for {$this->vehicle->displayName()}")
            ->greeting('There is an open recall on your vehicle')
            ->line("{$this->vehicle->displayName()} — {$this->recall->component}")
            ->line($this->recall->summary ?? '')
            ->action('See the details', route('vehicles.show', $this->vehicle))
            ->line('Recall work is carried out free of charge by a franchised dealer.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'recall',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->displayName(),
            'campaign' => $this->recall->campaign_number,
            'component' => $this->recall->component,
            'url' => route('vehicles.show', $this->vehicle),
        ];
    }
}
