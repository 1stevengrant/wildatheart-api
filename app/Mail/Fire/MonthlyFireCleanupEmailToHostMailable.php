<?php

namespace App\Mail\Fire;

use App\Models\EmailTemplate;
use App\Models\Event;
use App\Traits\BurnExtinguishFireActions;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MonthlyFireCleanupEmailToHostMailable extends Mailable implements ShouldQueue
{
    use BurnExtinguishFireActions, InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(public Event $event)
    {
        $this->emailTemplate = $this->getEmailTemplate('monthly-fire-cleanup-email-to-host');
        $this->extinguishLink = URL::signedRoute('fire.extinguish', ['event' => $this->event]);
        $this->burnLink = URL::signedRoute('fire.burn', ['event' => $this->event]);
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
        $content = str_replace('[host_name]', $this->event->host->given_name, $content);
        $content = str_replace('[button_inactive]', $this->getInactiveButtonComponent(), $content);
        $content = str_replace('[button_active]', $this->getActiveButtonComponent(), $content);
        $this->emailContent = \Str::markdown($content);

        return new Content(
            markdown: 'emails.fire.monthly-fire-cleanup-email-to-host',
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
