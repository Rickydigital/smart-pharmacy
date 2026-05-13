<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Smart Pharmacy Password Reset Code')
            ->greeting('Hello!')
            ->line('Use the code below to reset your Smart Pharmacy password.')
            ->line('Reset Code: ' . $this->otp)
            ->line('This code expires in 10 minutes.')
            ->line('If you did not request this, ignore this email.');
    }
}