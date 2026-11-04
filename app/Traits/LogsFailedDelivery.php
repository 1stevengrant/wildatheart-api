<?php

namespace App\Traits;

use Throwable;
use App\Models\Event;
use App\Models\EmailLog;
use App\Enums\EmailLogStatus;
use App\Support\EmailAddresses;

/**
 * Laravel calls failed() on a queued mailable once its job has exhausted
 * every retry, so each undelivered email is logged exactly once.
 */
trait LogsFailedDelivery
{
    public function failed(Throwable $exception): void
    {
        rescue(fn () => EmailLog::create([
            'delivery_status' => EmailLogStatus::Failed,
            'mailable' => static::class,
            'subject' => $this->resolveLoggedSubject(),
            'from' => EmailAddresses::formatRecipients($this->from),
            'to' => EmailAddresses::formatRecipients($this->to),
            'cc' => EmailAddresses::formatRecipients($this->cc),
            'bcc' => EmailAddresses::formatRecipients($this->bcc),
            'mailer' => $this->mailer,
            'event_id' => $this->resolveLoggedEventId(),
            'error' => $exception->getMessage(),
        ]));
    }

    protected function resolveLoggedSubject(): ?string
    {
        if ($this->subject) {
            return $this->subject;
        }

        if (! method_exists($this, 'envelope')) {
            return null;
        }

        try {
            return $this->envelope()->subject;
        } catch (Throwable) {
            return null;
        }
    }

    protected function resolveLoggedEventId(): ?string
    {
        $event = property_exists($this, 'event') && isset($this->event) ? $this->event : null;

        return $event instanceof Event ? $event->getKey() : null;
    }
}
