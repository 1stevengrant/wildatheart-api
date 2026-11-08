<?php

namespace App\Mail\Fire\Schedule;

use App\Models\Event;
use App\Mail\BaseMailable;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Shared base for the Fire schedule emails. The template is loaded in the constructor,
 * so a missing template fails before the job records that the email was sent.
 */
abstract class FireScheduleMailable extends BaseMailable implements ShouldQueue
{
    use FormatsEventHostNames, InteractsWithEmailTemplates, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent = '';

    public function __construct(public Event $event, public string $lastDate)
    {
        $this->emailTemplate = $this->getEmailTemplate($this->templateSlug());
    }

    abstract protected function templateSlug(): string;

    /**
     * @return array<string, string>
     */
    protected function replacements(): array
    {
        return [
            '[event_name]' => $this->event->name ?? '',
            '[event_host_name]' => $this->getAllHostNames(),
            '[last_date]' => CarbonImmutable::parse($this->lastDate)->format('l, F j'),
            '[button_edit_schedule]' => view('emails.components.button', [
                'url' => $this->event->scheduleEditUrl(),
                'text' => 'Edit My Schedule',
            ])->render(),
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: strtr($this->emailTemplate->subject, $this->replacements()),
        );
    }

    public function content(): Content
    {
        $this->emailContent = Str::markdown(strtr($this->emailTemplate->body, $this->replacements()));

        return new Content(
            markdown: 'emails.fire.schedule',
        );
    }
}
