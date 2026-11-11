<?php

namespace App\Mail\Event;

use App\Models\Event;
use App\Models\EventType;
use App\Mail\BaseMailable;
use App\Models\EmailTemplate;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class RetreatThankYouMailable extends BaseMailable implements ShouldQueue
{
    use FormatsEventHostNames, SerializesModels;

    /** @var array<string, string> */
    public static array $templateMap = [
        EventType::WAH_RETREAT => 'wah-retreat-thank-you',
        EventType::CAPTIVATING_CORE_RETREAT => 'captivating-retreat-thank-you',
        EventType::CAPTIVATING_CORE_RETREAT_1_DAY => '1-day-captivating-retreat-thank-you',
        EventType::BECOMING_A_KING => 'becoming-a-king-retreat-thank-you',
        EventType::SURVIVAL_SCHOOL => 'survival-school-thank-you',
    ];

    public string $emailContent;

    public function __construct(public Event $event, public EmailTemplate $emailTemplate)
    {
        //
    }

    /** @return array<int, string> */
    public static function eventTypeNames(): array
    {
        return array_keys(self::$templateMap);
    }

    public static function templateSlugFor(Event $event): ?string
    {
        return self::$templateMap[$event->eventType?->name] ?? null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailTemplate->subject,
        );
    }

    public function content(): Content
    {
        $hostNames = $this->emailTemplate->cc_co_hosts
            ? $this->getAllHostNames()
            : $this->event->user->given_name;

        $this->emailContent = str_replace(
            ['[event_host_name]', '[event_name]'],
            [$hostNames, $this->event->name],
            $this->emailTemplate->body,
        );

        return new Content(
            markdown: 'emails.events.retreat-thank-you',
        );
    }
}
