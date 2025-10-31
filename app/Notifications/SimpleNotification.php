<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SimpleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $title, private string $message)
    {
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
        ];
    }
}
