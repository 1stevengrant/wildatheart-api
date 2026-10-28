<?php

namespace App\Listeners;

use App\Models\Event;
use App\Models\EmailLog;
use App\Enums\EmailLogStatus;
use App\Support\EmailAddresses;
use Symfony\Component\Mime\Email;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Part\DataPart;

class LogSentEmailListener
{
    /**
     * Logging must never throw: the email has already been delivered, so a
     * failure here would fail the queued mail job and the queue would retry it,
     * sending a duplicate.
     */
    public function handle(MessageSent $event): void
    {
        rescue(fn () => $this->log($event));
    }

    protected function log(MessageSent $event): void
    {
        $message = $event->message;

        if (! $message instanceof Email) {
            return;
        }

        $relatedEvent = $event->data['event'] ?? null;

        EmailLog::create([
            'delivery_status' => EmailLogStatus::Sent,
            'mailable' => $event->data['__laravel_mailable'] ?? null,
            'subject' => $message->getSubject(),
            'from' => EmailAddresses::format($message->getFrom()),
            'to' => EmailAddresses::format($message->getTo()),
            'cc' => EmailAddresses::format($message->getCc()),
            'bcc' => EmailAddresses::format($message->getBcc()),
            'html_body' => $message->getHtmlBody(),
            'attachments' => collect($message->getAttachments())
                ->map(fn (DataPart $attachment): array => [
                    'name' => $attachment->getFilename(),
                    'content_type' => $attachment->getContentType(),
                    'size' => mb_strlen($attachment->getBody(), '8bit'),
                ])
                ->all(),
            'mailer' => $event->data['mailer'] ?? null,
            'message_id' => $event->sent->getMessageId(),
            'event_id' => $relatedEvent instanceof Event ? $relatedEvent->getKey() : null,
        ]);
    }
}
