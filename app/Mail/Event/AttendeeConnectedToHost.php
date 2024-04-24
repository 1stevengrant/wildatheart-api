<?php

namespace App\Mail\Event;

use App\Models\Attendee;
use App\Models\EmailTemplate;
use App\Models\Event;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

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
        $subject = str_replace('[event_title]', $this->event->title, $subject);

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
        $content = str_replace('[host_name]', $this->event->host->given_name, $content);
        $content = str_replace('[event_title]', $this->event->name, $content);
        $content = str_replace('[attendee_name]', $this->attendee->given_name, $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.events.attendee-connected-to-host',
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
