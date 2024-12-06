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

class SendWelcomeEmail extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    public function __construct(public Event $event)
    {
        $this->emailTemplate = $this->getEmailTemplate('send-welcome-email');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailTemplate->subject,
        );
    }

    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $content = str_replace('[event_host_name]', $this->event->host->given_name, $content);
        $content = str_replace('[event_url]', $this->event->url, $content);
        $content = str_replace('[email_address]', config('mail.from.address'), $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.events.send-welcome-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
