<?php

namespace App\Console\Commands\Event;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Mail\Event\RetreatThankYouMailable;
use App\Jobs\Event\SendRetreatThankYouEmailJob;

class SendRetreatThankYouEmailsCommand extends Command
{
    public const CATCH_UP_DAYS = 7;

    protected $signature = 'event:send-retreat-thank-you
                            {--dry-run : Preview eligible retreats without sending emails}';

    protected $description = 'Send a thank-you email with feedback forms to hosts of retreats that ended recently.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info($isDryRun ? 'Dry run, previewing eligible retreats...' : 'Sending retreat thank-you emails...');

        $count = 0;

        Event::query()
            ->with(['user', 'managers'])
            ->ofEventTypes(RetreatThankYouMailable::eventTypeNames())
            ->published()
            ->endedWithinTheLastDays(self::CATCH_UP_DAYS)
            ->whereNull('post_event_email_sent_at')
            ->chunkById(100, function (Collection $events) use ($isDryRun, &$count) {
                $events->each(function (Event $event) use ($isDryRun, &$count) {
                    $this->line("  - [{$event->id}] {$event->name} (ended {$event->end_date->toDateString()}, host: {$event->user?->email})");

                    if (! $isDryRun) {
                        SendRetreatThankYouEmailJob::dispatch($event);
                    }

                    $count++;
                });
            });

        $this->comment($isDryRun ? "Found {$count} eligible retreat(s)." : "Queued {$count} retreat thank-you email(s).");

        return self::SUCCESS;
    }
}
