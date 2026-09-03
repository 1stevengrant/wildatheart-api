<?php

namespace App\Mail;

use App\Models\ContactInvitation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactInvitationMail extends BaseMailable implements ShouldQueue
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ContactInvitation $invitation,
        public string $token,
        public bool $accountExisted,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->invitation->invitedBy->fullname} wants to add you as a contact",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-invitation',
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
