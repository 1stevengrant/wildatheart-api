<?php

namespace App\Mail;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\EventOwnershipTransfer;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class OwnershipTransferInvitationMail extends BaseMailable implements ShouldQueue
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Event $event,
        public string $transferredByName,
        public ?EventOwnershipTransfer $transfer = null,
        public bool $userExists = false,
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
            subject: "You've been invited to take ownership of {$this->event->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ownership-transfer-invitation',
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
