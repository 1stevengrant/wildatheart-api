<?php

namespace App\Mail\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactHostVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your email to connect with a host',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.event.contact-host-verification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
