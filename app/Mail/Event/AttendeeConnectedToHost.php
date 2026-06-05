<?php

namespace App\Mail\Event;

use App\Models\Event;
use App\Models\Attendee;
use App\Models\EventType;
use App\Mail\BaseMailable;
use App\Models\EmailTemplate;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttendeeConnectedToHost extends BaseMailable implements ShouldQueue
{
    use FormatsEventHostNames, InteractsWithEmailTemplates, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Event $event,
        public Attendee $attendee
    ) {
        $this->emailTemplate = $this->getEmailTemplate('attendee-connected-to-host');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->emailTemplate->subject;

        $subject = str_replace('[event_title]', $this->event->eventType->name, $subject);

        return new Envelope(
            subject: $subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $eventName = $this->event->eventType->name;
        $content = str_replace('[host_name]', $this->getAllHostNames(), $content);
        $link = route('view.event', $this->event);
        if ($eventName === EventType::FIRES) {
            $eventName = '<a href="' . $link . '">' . $this->event->name . ' Fire</a>';
        } else {
            $eventName = '<a href="' . $link . '">' . $this->event->eventType->name . '</a>';
        }
        $content = str_replace('[event_title]', $eventName, $content);
        $content = str_replace('[attendee_name]', $this->attendee->given_name, $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.events.attendee-connected-to-host',
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
