<?php

namespace App\Mail\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event)
    {

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Event is live!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.events.send-welcome-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
