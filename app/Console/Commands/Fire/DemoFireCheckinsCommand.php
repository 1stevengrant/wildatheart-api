<?php

namespace App\Console\Commands\Fire;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Jobs\Fire\SendFireCheckinsEmailJob;
use App\Jobs\Fire\AutoUnpublishUnconfirmedFiresJob;
use App\Jobs\Fire\SendFireCheckinsReminderEmailJob;

class DemoFireCheckinsCommand extends Command
{
    protected $signature = 'fire:demo-checkins
                            {event? : The event ID to demo with}
                            {--stage=status : Stage to demo: status, initial, reminder, unpublish, full}
                            {--send : Actually send emails (otherwise dry-run)}
                            {--reset : Reset the event timestamps to start fresh}';

    protected $description = 'Demo the fire check-in flow without waiting for days.';

    public function handle(): int
    {
        $event = $this->getEvent();

        if (! $event) {
            return self::FAILURE;
        }

        $stage = $this->option('stage');

        if ($this->option('reset')) {
            $this->resetEvent($event);
        }

        return match ($stage) {
            'status' => $this->showStatus($event),
            'initial' => $this->demoInitialEmail($event),
            'reminder' => $this->demoReminderEmail($event),
            'unpublish' => $this->demoUnpublish($event),
            'full' => $this->demoFullFlow($event),
            default => $this->showStatus($event),
        };
    }

    protected function getEvent(): ?Event
    {
        $eventId = $this->argument('event');

        if ($eventId) {
            $event = Event::with(['user', 'managers'])->find($eventId);

            if (! $event) {
                $this->error("Event {$eventId} not found.");

                return null;
            }

            return $event;
        }

        // Show list of fires to choose from
        $fires = Event::query()
            ->with(['user'])
            ->allFires()
            ->published()
            ->limit(10)
            ->get();

        if ($fires->isEmpty()) {
            $this->error('No published fires found.');

            return null;
        }

        $this->info('Select a fire to demo:');
        $fires->each(function ($fire, $index) {
            $this->line("  [{$fire->id}] {$fire->name} (Host: {$fire->user->given_name})");
        });

        $eventId = $this->ask('Enter event ID');

        return Event::with(['user', 'managers'])->find($eventId);
    }

    protected function showStatus(Event $event): int
    {
        $this->newLine();
        $this->info("=== Fire Check-in Status: {$event->name} ===");
        $this->newLine();

        $this->table(
            ['Field', 'Value', 'Meaning'],
            [
                ['Host', $event->user->given_name, ''],
                ['Managers', $event->managers->pluck('given_name')->implode(', ') ?: 'None', ''],
                ['Published', $event->approved_at ? 'Yes' : 'No', ''],
                ['Last Updated', $event->updated_at?->diffForHumans() ?? 'Never', ''],
                ['Initial Email Sent', $event->last_checkin_email_sent_at?->diffForHumans() ?? 'Never', $this->getInitialEmailStatus($event)],
                ['Reminder Sent', $event->last_checkin_reminder_sent_at?->diffForHumans() ?? 'Never', $this->getReminderStatus($event)],
                ['Last Confirmed', $event->last_checkin_confirmed_at?->diffForHumans() ?? 'Never', ''],
                ['Auto-unpublished', $event->auto_unpublished ? 'Yes' : 'No', ''],
            ]
        );

        $this->newLine();
        $this->info('Next action: ' . $this->getNextAction($event));
        $this->newLine();

        $this->line('Available demo commands:');
        $this->line("  php artisan fire:demo-checkins {$event->id} --stage=initial    # Simulate 90+ days inactive");
        $this->line("  php artisan fire:demo-checkins {$event->id} --stage=reminder   # Simulate 7+ days after initial");
        $this->line("  php artisan fire:demo-checkins {$event->id} --stage=unpublish  # Simulate 3+ days after reminder");
        $this->line("  php artisan fire:demo-checkins {$event->id} --stage=full       # Run full flow interactively");
        $this->line("  php artisan fire:demo-checkins {$event->id} --reset            # Reset timestamps");
        $this->newLine();

        return self::SUCCESS;
    }

    protected function demoInitialEmail(Event $event): int
    {
        $this->info('=== Demo: Initial Check-in Email ===');
        $this->newLine();

        // Simulate 91 days ago
        $event->update([
            'updated_at' => now()->subDays(91),
            'last_checkin_email_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        $this->line('Simulated: Event inactive for 91 days, no previous check-in email.');
        $this->newLine();

        if ($this->option('send')) {
            $this->warn('Sending actual email...');
            (new SendFireCheckinsEmailJob)->handle();
            $event->refresh();
            $this->info("✓ Initial check-in email sent to {$event->user->email}");
            if ($event->managers->isNotEmpty()) {
                $this->info("  CC'd: " . $event->managers->pluck('email')->implode(', '));
            }
        } else {
            $this->warn('[DRY RUN] Would send initial check-in email to:');
            $this->line("  To: {$event->user->email} ({$event->user->given_name})");
            if ($event->managers->isNotEmpty()) {
                $this->line('  CC: ' . $event->managers->pluck('email')->implode(', '));
            }
            $this->newLine();
            $this->line('Add --send flag to actually send the email.');
        }

        return self::SUCCESS;
    }

    protected function demoReminderEmail(Event $event): int
    {
        $this->info('=== Demo: 7-Day Reminder Email ===');
        $this->newLine();

        // Simulate: initial email sent 8 days ago, no reminder yet
        $event->update([
            'last_checkin_email_sent_at' => now()->subDays(8),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        $this->line('Simulated: Initial email sent 8 days ago, no reminder yet, no confirmation.');
        $this->newLine();

        if ($this->option('send')) {
            $this->warn('Sending actual email...');
            (new SendFireCheckinsReminderEmailJob)->handle();
            $event->refresh();
            $this->info("✓ Reminder email sent to {$event->user->email}");
            if ($event->managers->isNotEmpty()) {
                $this->info("  CC'd: " . $event->managers->pluck('email')->implode(', '));
            }
        } else {
            $this->warn('[DRY RUN] Would send reminder email to:');
            $this->line("  To: {$event->user->email} ({$event->user->given_name})");
            if ($event->managers->isNotEmpty()) {
                $this->line('  CC: ' . $event->managers->pluck('email')->implode(', '));
            }
            $this->newLine();
            $this->line('Add --send flag to actually send the email.');
        }

        return self::SUCCESS;
    }

    protected function demoUnpublish(Event $event): int
    {
        $this->info('=== Demo: Auto-Unpublish ===');
        $this->newLine();

        // Simulate: initial email 14 days ago, reminder 4 days ago, no confirmation
        $event->update([
            'last_checkin_email_sent_at' => now()->subDays(14),
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        $this->line('Simulated: Initial email 14 days ago, reminder 4 days ago, no confirmation.');
        $this->newLine();

        if ($this->option('send')) {
            $this->warn('Unpublishing and sending notification...');
            (new AutoUnpublishUnconfirmedFiresJob)->handle();
            $event->refresh();
            $this->info('✓ Fire unpublished');
            $this->info("✓ Notification sent to {$event->user->email}");
            if ($event->managers->isNotEmpty()) {
                $this->info("  CC'd: " . $event->managers->pluck('email')->implode(', '));
            }
        } else {
            $this->warn('[DRY RUN] Would unpublish fire and send notification to:');
            $this->line("  To: {$event->user->email} ({$event->user->given_name})");
            if ($event->managers->isNotEmpty()) {
                $this->line('  CC: ' . $event->managers->pluck('email')->implode(', '));
            }
            $this->newLine();
            $this->line('Add --send flag to actually unpublish and send the email.');
        }

        return self::SUCCESS;
    }

    protected function demoFullFlow(Event $event): int
    {
        $this->info('=== Demo: Full Check-in Flow ===');
        $this->newLine();

        if (! $this->option('send')) {
            Mail::fake();
            $this->warn('[DRY RUN MODE - No emails will be sent]');
            $this->newLine();
        }

        // Step 1: Initial email
        $this->info('Step 1: Initial Check-in Email (Day 0)');
        $this->line('Simulating: Fire inactive for 91 days...');

        $event->update([
            'updated_at' => now()->subDays(91),
            'last_checkin_email_sent_at' => null,
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
            'approved_at' => now(),
        ]);

        (new SendFireCheckinsEmailJob)->handle();
        $event->refresh();

        $this->info('  ✓ Initial email ' . ($this->option('send') ? 'sent' : 'would be sent'));
        $this->line("    To: {$event->user->email}");
        $this->newLine();

        if (! $this->confirm('Continue to reminder stage? (Simulates 7 days passing)')) {
            return self::SUCCESS;
        }

        // Step 2: Reminder email
        $this->info('Step 2: Reminder Email (Day 7)');
        $this->line('Simulating: 7 days since initial email, no response...');

        $event->update([
            'last_checkin_email_sent_at' => now()->subDays(8),
        ]);

        (new SendFireCheckinsReminderEmailJob)->handle();
        $event->refresh();

        $this->info('  ✓ Reminder email ' . ($this->option('send') ? 'sent' : 'would be sent'));
        $this->newLine();

        if (! $this->confirm('Continue to unpublish stage? (Simulates 3 more days passing)')) {
            return self::SUCCESS;
        }

        // Step 3: Auto-unpublish
        $this->info('Step 3: Auto-Unpublish (Day 10)');
        $this->line('Simulating: 3 days since reminder, no response...');

        $event->update([
            'last_checkin_email_sent_at' => now()->subDays(14),
            'last_checkin_reminder_sent_at' => now()->subDays(4),
        ]);

        (new AutoUnpublishUnconfirmedFiresJob)->handle();
        $event->refresh();

        $this->info('  ✓ Fire ' . ($this->option('send') ? 'unpublished' : 'would be unpublished'));
        $this->info('  ✓ Notification ' . ($this->option('send') ? 'sent' : 'would be sent'));
        $this->newLine();

        $this->info('=== Flow Complete ===');

        if (! $this->option('send')) {
            $this->newLine();
            $this->warn('This was a dry run. Add --send to actually send emails and unpublish.');

            // Reset for dry run
            if ($this->confirm('Reset event to original state?', true)) {
                $this->resetEvent($event);
                $this->info('Event reset.');
            }
        }

        return self::SUCCESS;
    }

    protected function resetEvent(Event $event): void
    {
        $event->update([
            'updated_at' => now(),
            'last_checkin_email_sent_at' => null,
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
            'auto_unpublished_at' => null,
            'approved_at' => now(),
        ]);

        $this->info("Reset timestamps for: {$event->name}");
    }

    protected function getInitialEmailStatus(Event $event): string
    {
        if (! $event->last_checkin_email_sent_at) {
            return 'Eligible if inactive 90+ days';
        }

        $daysSince = $event->last_checkin_email_sent_at->diffInDays(now());

        if ($daysSince >= 90) {
            return 'Eligible for new cycle';
        }

        return "In active cycle ({$daysSince} days ago)";
    }

    protected function getReminderStatus(Event $event): string
    {
        if (! $event->last_checkin_email_sent_at) {
            return 'Waiting for initial email';
        }

        if ($event->last_checkin_reminder_sent_at) {
            $daysSince = $event->last_checkin_reminder_sent_at->diffInDays(now());

            return $daysSince >= 3 ? 'Eligible for unpublish' : "Waiting ({$daysSince}/3 days)";
        }

        $daysSince = $event->last_checkin_email_sent_at->diffInDays(now());

        if ($daysSince >= 90) {
            return 'Skipped - new cycle needed';
        }

        if ($daysSince >= 7) {
            return 'Eligible for reminder';
        }

        return "Waiting ({$daysSince}/7 days)";
    }

    protected function getNextAction(Event $event): string
    {
        if (! $event->approved_at) {
            return 'None - event is unpublished';
        }

        if ($event->last_checkin_confirmed_at && $event->last_checkin_confirmed_at->diffInDays(now()) < 90) {
            return 'None - recently confirmed';
        }

        if (! $event->last_checkin_email_sent_at || $event->last_checkin_email_sent_at->diffInDays(now()) >= 90) {
            return 'Initial check-in email (90+ days inactive)';
        }

        if (! $event->last_checkin_reminder_sent_at && $event->last_checkin_email_sent_at->diffInDays(now()) >= 7) {
            return 'Reminder email (7+ days since initial)';
        }

        if ($event->last_checkin_reminder_sent_at && $event->last_checkin_reminder_sent_at->diffInDays(now()) >= 3) {
            return 'Auto-unpublish (3+ days since reminder)';
        }

        return 'Waiting...';
    }
}
