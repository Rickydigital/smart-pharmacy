<?php

namespace App\Notifications;

use App\Models\InventoryAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public InventoryAlert $alert)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];

        /*
        // Later if email is needed:
        return ['database', 'mail'];
        */
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'alert_no' => $this->alert->alert_no,
            'alert_type' => $this->alert->alert_type,
            'severity' => $this->alert->severity,
            'title' => $this->alert->title,
            'message' => $this->alert->message,
            'url' => route('inventory-alerts.index', [
                'status' => 'open',
                'alert_type' => $this->alert->alert_type,
            ]),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->alert->title)
            ->line($this->alert->message)
            ->action('View Alert Center', route('inventory-alerts.index'));
    }
}