<?php

namespace App\Mail\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Traits\LogsFailedDelivery;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserConnectedToHost extends Mailable implements ShouldQueue
{
    use FormatsEventHostNames, InteractsWithEmailTemplates, LogsFailedDelivery, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    public function __construct(
        public Event $event,
        public User $user
    ) {
        $this->emailTemplate = $this->getEmailTemplate('attendee-connected-to-host');
    }

    public function envelope(): Envelope
    {
        $subject = $this->emailTemplate->subject;
        $subject = str_replace('[event_title]', $this->event->eventType->name, $subject);

        return new Envelope(
            from: $this->templateSender(),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $eventName = $this->event->eventType->name;
        $content = str_replace('[host_name]', e($this->getAllHostNames()), $content);
        $link = $this->event->attend_url;
        if ($eventName === EventType::FIRES) {
            $eventName = '<a href="' . $link . '">' . e($this->event->name) . ' Fire</a>';
        } else {
            $eventName = '<a href="' . $link . '">' . e($this->event->eventType->name) . '</a>';
        }
        $content = str_replace('[event_title]', $eventName, $content);
        $content = str_replace('[attendee_name]', e($this->user->given_name ?: 'Friend'), $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.events.attendee-connected-to-host',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
