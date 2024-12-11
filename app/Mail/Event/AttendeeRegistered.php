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

class AttendeeRegistered extends Mailable implements ShouldQueue
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
        $this->emailTemplate = $this->getEmailTemplate('attendee-registered');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $content = $this->emailTemplate->body;
        $subject = $this->emailTemplate->subject;
        $subject = str_replace('[attendee_name]', $this->attendee->fullname, $subject);

        return new Envelope(
            replyTo: $this->attendee->email,
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $content = str_replace('[host_name]', $this->event->host->given_name, $content);
        $content = str_replace('[attendee_fullname]', $this->attendee->given_name, $content);
        $content = str_replace('[attendee_name]', $this->attendee->given_name, $content);
        $content = str_replace('[attendee_email]', $this->attendee->email, $content);
        $content = str_replace('[attendee_phone]', $this->attendee->phone ?? 'no phone number', $content);
        $content = str_replace('[attendee_city]', $this->attendee->city, $content);
        $content = str_replace('[attendee_state]', $this->attendee->state ?? 'no state', $content);
        $eventName = $this->event->eventType->name;
        $link = route('view.event', $this->event);
        if ($eventName === EventType::FIRES) {
            $eventName = 'Fire (<a href="'.$link.'">'.$this->event->name.' Fire</a>)';
        } else {
            $eventName = '<a href="'.$link.'">'.$this->event->eventType->name.'</a>';
        }
        $content = str_replace('[event_name]', $eventName, $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.attendee-registered',
        );
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
