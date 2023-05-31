<?php

namespace App\Mail\Campfire;

use App\Models\Campfire;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Campfire $campfire)
    {

    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Welcome Email',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campfires.send-welcome-email',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path('downloads/WaH-Fires_Firestarter-Manual-v2.pdf'))
                ->as('firestarter.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
