<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventAutoUnpublishedMail extends BaseMailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(public Event $event)
    {
        $this->emailTemplate = $this->getEmailTemplate(EmailTemplate::FIRE_MADE_INACTIVE);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->emailTemplate->subject;
        $subject = str_replace('[event_name]', $this->event->name, $subject);

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $this->emailContent = \Str::markdown($content);

        return new Content(
            markdown: 'emails.event-auto-unpublished',
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
