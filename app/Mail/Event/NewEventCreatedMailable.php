<?php

namespace App\Mail\Event;

use App\Models\EmailTemplate;
use App\Models\Event;
use App\Models\EventType;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEventCreatedMailable extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(public Event $event)
    {
        match ($this->event->eventType->name) {
            EventType::WAH_RETREAT => $this->emailTemplate = $this->getEmailTemplate('wah-retreat-created'),
            EventType::WAH_EXPERIENCE => $this->emailTemplate = $this->getEmailTemplate('wild-at-heart-experience-created'),
            EventType::CAPTIVATING_EXPERIENCE => $this->emailTemplate = $this->getEmailTemplate('captivating-experience-created'),
            EventType::CAPTIVATING_CORE_RETREAT => $this->emailTemplate = $this->getEmailTemplate('captivating-retreat-created'),
            EventType::BECOMING_A_KING_EXPERIENCE => $this->emailTemplate = $this->getEmailTemplate('becoming-a-king-experience-created'),
            EventType::BECOMING_A_KING => $this->emailTemplate = $this->getEmailTemplate('becoming-a-king-retreat-event-created'),
            EventType::SURVIVAL_SCHOOL => $this->emailTemplate = $this->getEmailTemplate('survival-school-event-created'),
            EventType::FIRES => $this->emailTemplate = $this->getEmailTemplate('send-welcome-email'),
            EventType::CAPTIVATING_FIRE => $this->emailTemplate = $this->getEmailTemplate('captivating-fires-welcome-email'),
        };
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
        $content = str_replace('[event_url]', $this->event->url, $content);
        $content = str_replace('[event_host_name]', $this->event->host->given_name, $content);
        $this->emailContent = \Str::markdown($content);

        return new Content(
            markdown: 'mail.event.new-event-created',
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
