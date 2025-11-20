<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\User\ProfileUpdateReminder;

class SendProfileUpdateReminders extends Command
{
    protected $signature = 'users:send-profile-update-reminder
                            {--dry-run : Preview affected users without sending emails}
                            {--limit= : Limit number of emails sent}';

    protected $description = 'Send email reminders to users without given_name or family_name';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        $this->info('Finding users without complete names...');

        $query = User::query()
            ->where(function ($q) {
                $q->whereNull('given_name')
                    ->orWhereNull('family_name');
            })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', User::ROLE_ADMIN);
            });

        if ($limit) {
            $query->limit((int) $limit);
        }

        $users = $query->get();

        $count = $users->count();

        if ($count === 0) {
            $this->info('No users found needing profile updates.');

            return Command::SUCCESS;
        }

        $this->info("Found {$count} user(s) without complete names.");

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN MODE - No emails will be sent');
            $this->newLine();

            $this->table(
                ['ID', 'Email', 'Given Name', 'Family Name'],
                $users->map(fn ($user) => [
                    $user->id,
                    $user->email,
                    $user->given_name ?? '(null)',
                    $user->family_name ?? '(null)',
                ])
            );

            return Command::SUCCESS;
        }

        $this->info('Sending emails...');
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $sent = 0;
        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new ProfileUpdateReminder($user));
                $sent++;
            } catch (\Exception $e) {
                $this->error("\nFailed to send email to {$user->email}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Successfully sent {$sent} email(s).");

        return Command::SUCCESS;
    }
}
