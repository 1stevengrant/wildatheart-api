<?php

namespace App\Mail;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class HostRemovedMail extends BaseMailable implements ShouldQueue
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Event $event,
        public User $host,
        public User $removedBy,
    ) {
        $this->event->formatted_start_date = $event->start_date
            ? Carbon::parse($event->start_date)->format('F j, Y')
            : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been removed as a host from {$this->event->emailName()}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.host-removed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
