<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailChangeVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('verify-email-change', ['token' => $this->token]));

        return (new MailMessage)
            ->subject('Verify Email Address Change')
            ->greeting('Hello ' . $notifiable->given_name . '!')
            ->line('You recently requested to change your email address. Please click the button below to verify your new email address.')
            ->action('Verify Email Address', $url)
            ->line('If you did not request this change, no further action is required.')
            ->line('This verification link will expire in 60 minutes.');
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
