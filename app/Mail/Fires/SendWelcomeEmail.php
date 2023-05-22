<?php

namespace App\Mail\Fires;

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
            markdown: 'emails.fires.send-welcome-email',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path('app/downloads/firestarter.pdf'))
                ->as('firestarter.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
