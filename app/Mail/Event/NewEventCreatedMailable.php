<?php

namespace App\Mail\Event;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Jobs\Event\MarkWelcomeSentJob;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewEventCreatedMailable extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public ?EmailTemplate $emailTemplate = null;

    public string $emailContent;

    public static array $templateMap = [
        EventType::WAH_RETREAT => 'wah-retreat-created',
        EventType::WAH_EXPERIENCE => 'wild-at-heart-experience-created',
        EventType::CAPTIVATING_EXPERIENCE => 'captivating-experience-created',
        EventType::CAPTIVATING_CORE_RETREAT => 'captivating-retreat-created',
        EventType::CAPTIVATING_CORE_RETREAT_1_DAY => '1-day-captivating-retreat-created',
        EventType::BECOMING_A_KING_EXPERIENCE => 'becoming-a-king-experience-created',
        EventType::BECOMING_A_KING => 'becoming-a-king-retreat-event-created',
        EventType::SURVIVAL_SCHOOL => 'survival-school-created',
        EventType::FIRES => 'send-fires-welcome-email',
        EventType::CAPTIVATING_FIRE => 'captivating-fires-welcome-email',
    ];

    public static function hasTemplateFor(string $eventTypeName): bool
    {
        return isset(self::$templateMap[$eventTypeName]);
    }

    /**
     * Create a new message instance.
     */
    public function __construct(public Event $event)
    {
        $templateSlug = self::$templateMap[$this->event->eventType->name] ?? null;

        if (! $templateSlug) {
            return;
        }

        $this->emailTemplate = $this->getEmailTemplate($templateSlug);
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
        $content = str_replace('[event_host_name]', $this->event->user->given_name, $content);

        $this->emailContent = Str::markdown($content);

        return new Content(
            markdown: 'emails.events.new-event-created'
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

    public function send($mailer): void
    {
        parent::send($mailer);
        MarkWelcomeSentJob::dispatch($this->event->id);
    }
}
