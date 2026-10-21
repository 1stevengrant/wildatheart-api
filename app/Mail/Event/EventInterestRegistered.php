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

class EventInterestRegistered extends Mailable implements ShouldQueue
{
    use FormatsEventHostNames, InteractsWithEmailTemplates, LogsFailedDelivery, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    public function __construct(
        public Event $event,
        public User $user
    ) {
        $this->emailTemplate = $this->getEmailTemplate('attendee-registered');
    }

    public function envelope(): Envelope
    {
        $subject = $this->emailTemplate->subject;
        $subject = str_replace('[attendee_name]', $this->user->fullname, $subject);

        return new Envelope(
            replyTo: $this->user->email,
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $content = str_replace('[host_name]', e($this->getAllHostNames()), $content);
        $content = str_replace('[attendee_fullname]', e($this->user->fullname), $content);
        $content = str_replace('[attendee_name]', e($this->user->given_name ?: $this->user->fullname), $content);
        $content = str_replace('[attendee_email]', e($this->user->email), $content);
        $content = str_replace('[attendee_phone]', e($this->user->phone ?? 'no phone number'), $content);
        $content = str_replace('[attendee_city]', e($this->user->city ?? ''), $content);
        $content = str_replace('[attendee_state]', e($this->user->state ?? 'no state'), $content);

        $eventName = $this->event->eventType->name;
        $link = $this->event->attend_url;
        if ($eventName === EventType::FIRES) {
            $eventName = 'Fire (<a href="' . $link . '">' . e($this->event->name) . ' Fire</a>)';
        } else {
            $eventName = '<a href="' . $link . '">' . e($this->event->eventType->name) . '</a>';
        }
        $content = str_replace('[event_name]', $eventName, $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.attendee-registered',
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
