<?php

namespace App\Mail\Event;

use App\Models\Event;
use App\Models\Attendee;
use App\Models\EventType;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttendeeConnectedToHost extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

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

    private function getAllHostNames(): string
    {
        $hosts = collect([$this->event->user]);

        if ($this->event->managers->isNotEmpty()) {
            $hosts = $hosts->merge($this->event->managers);
        }

        $names = $hosts->map(fn ($host) => $host->given_name)->filter();

        if ($names->count() <= 1) {
            return $names->first() ?? '';
        }

        if ($names->count() === 2) {
            return $names->implode(' and ');
        }

        $lastHost = $names->pop();

        return $names->implode(', ') . ', and ' . $lastHost;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
