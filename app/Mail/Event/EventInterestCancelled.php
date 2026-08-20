<?php

namespace App\Mail\Event;

use App\Models\User;
use App\Models\Event;
use App\Mail\BaseMailable;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventInterestCancelled extends BaseMailable implements ShouldQueue
{
    use FormatsEventHostNames, SerializesModels;

    public string $hostNames;

    public function __construct(
        public Event $event,
        public User $user,
    ) {
        $this->hostNames = $this->getAllHostNames();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->user->email,
            subject: "{$this->user->fullname} cancelled their interest in {$this->event->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.events.event-interest-cancelled',
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
