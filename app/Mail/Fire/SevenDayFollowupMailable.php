<?php

namespace App\Mail\Fire;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\BurnExtinguishFireActions;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class SevenDayFollowupMailable extends Mailable implements ShouldQueue
{
    use BurnExtinguishFireActions, FormatsEventHostNames, InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(public $event)
    {
        $this->emailTemplate = $this->getEmailTemplate('fire-deactivating-soon');
        $this->extinguishLink = URL::signedRoute('fire.extinguish', ['event' => $this->event]);
        $this->burnLink = URL::signedRoute('fire.burn', ['event' => $this->event]);
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
        $content = str_replace('[event_host_name]', $this->getAllHostNames(), $content);
        $content = str_replace('[button_inactive]', $this->getInactiveButtonComponent(), $content);
        $content = str_replace('[button_active]', $this->getActiveButtonComponent(), $content);
        $this->emailContent = \Str::markdown($content);

        return new Content(
            markdown: 'emails.fire.seven-day-followup',
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
