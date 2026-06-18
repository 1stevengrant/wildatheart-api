<?php

namespace App\Mail;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\EventManagerInvitation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class HostInvitationMail extends BaseMailable implements ShouldQueue
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Event $event,
        public string $invitedByName,
        public ?EventManagerInvitation $invitation = null,
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
            subject: "You've been invited to host {$this->event->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.host-invitation',
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
