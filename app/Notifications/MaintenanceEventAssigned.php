<?php

namespace App\Notifications;

use App\Models\MaintenanceEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceEventAssigned extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MaintenanceEvent $event,
        public string $type,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {

        $tz = auth()->user()->timezone;

        $when = $this->event->performedAtIn($tz)?->format('Y-m-d H:i');
        // $when = optional($this->event->performed_at)->timezone($timezone)->format('Y-m-d H:i');

        $message = match ($this->type) {
            'new_event' => "You have been assigned a new maintenance #{$this->event->event_id} at {$when}.",
            'new_time'  => "Maintenance #{$this->event->event_id} was rescheduled to {$when}.",
            default     => "Maintenance #{$this->event->event_id} was updated.",
        };

        return [
            'type' => 'maintenance_event.event_assigned',
            'event_id' => $this->event->event_id,
            'tree_id' =>  $this->event->tree_id,
            'performed_at' =>  $this->event->performed_at,
            'title' =>  'Maintenance Event',
            'message' => $message,
            'url' => route('calendar.index', [
                'date' => optional($this->event->performed_at)->format('Y-m-d'),
                'view' => 'day',
            ]),
        ];
    }
}
