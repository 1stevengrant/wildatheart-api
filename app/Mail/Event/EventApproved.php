<?php

namespace App\Mail\Event;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\InteractsWithEmailTemplates;

class EventApproved extends Mailable
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(public \App\Models\Event $event)
    {
        $this->emailTemplate = $this->getEmailTemplate('event-approved');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailTemplate->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $content = str_replace('[event_host_name]', $this->event->host->given_name, $content);
        $content = str_replace('[Fire Link]', "[Fire Link]({$this->event->url})", $content);
        $content = str_replace('[email_address]', config('mail.from.address'), $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.events.event-approved',
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
