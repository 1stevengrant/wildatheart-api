<?php

namespace App\Mail\V1;

use Carbon\Carbon;
use App\Models\Event;
use App\Mail\BaseMailable;
use App\Models\EventManagerInvitation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Co-host invitation sent from the v1 host API. Its link opens the invitation on the Astro
 * frontend; the legacy dashboard keeps sending App\Mail\HostInvitationMail.
 */
class HostInvitationMail extends BaseMailable implements ShouldQueue
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Event $event,
        public string $invitedByName,
        public EventManagerInvitation $invitation,
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
            subject: "You've been invited to host {$this->event->emailName()}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.v1.host-invitation',
            with: [
                'acceptUrl' => mb_rtrim((string) config('app.frontend_url'), '/') . '/accept-host-invitation/' . $this->invitation->id,
            ],
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
