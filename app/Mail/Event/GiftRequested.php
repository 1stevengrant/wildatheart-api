<?php

namespace App\Mail\Event;

use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class GiftRequested extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    public function __construct(public Event $event)
    {
        $this->emailTemplate = $this->getEmailTemplate('gift-requested');
    }

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
        $content = str_replace('[event_host_name]', $this->event->user->given_name, $content);
        $content = str_replace('[email_address]', config('mail.from.address'), $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.events.gift-requested',
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
