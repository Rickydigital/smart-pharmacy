<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeAccountCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $username,
        public string $plainPassword
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Smart Pharmacy Account')
            ->greeting('Hello ' . ($notifiable->first_name ?? 'User') . ',')
            ->line('Your pharmacy system account has been created.')
            ->line('Use the credentials below to sign in:')
            ->line('Username: ' . $this->username)
            ->line('Password: ' . $this->plainPassword)
            ->action('Login', route('login'))
            ->line('For security, please change your password from your profile after login.');
    }
}