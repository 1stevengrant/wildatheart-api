<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\Events\JobFailed;
use Spatie\FailedJobMonitor\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;

/**
 * Sent from the worker's JobFailed listeners, which run before the one that
 * writes to failed_jobs. Sending synchronously meant a Slack 429 during a burst
 * of failures threw out of that chain and the failure was never recorded, so
 * this is queued and only carries the strings it needs (the event holds the
 * live queue job, which can't be serialized).
 */
class FailedJobNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [5, 15, 30, 60];

    public string $jobName;

    public string $jobBody;

    public string $exceptionMessage;

    public string $exceptionTrace;

    public function setEvent(JobFailed $event): self
    {
        $this->jobName = $event->job->resolveName();
        $this->jobBody = $event->job->getRawBody();
        $this->exceptionMessage = $event->exception->getMessage();
        $this->exceptionTrace = $event->exception->getTraceAsString();

        return $this;
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $this->jobName !== self::class;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('A job failed at ' . config('app.url'))
            ->line("Exception message: {$this->exceptionMessage}")
            ->line("Job class: {$this->jobName}")
            ->line("Job body: {$this->jobBody}")
            ->line("Exception: {$this->exceptionTrace}");
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content('A job failed at ' . config('app.url'))
            ->attachment(function (SlackAttachment $attachment): void {
                $attachment->fields([
                    'Exception message' => $this->exceptionMessage,
                    'Job class' => $this->jobName,
                    'Job body' => $this->jobBody,
                    'Exception' => $this->exceptionTrace,
                ]);
            });
    }
}
