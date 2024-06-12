<?php

namespace App\Console\Commands;

use App\Models\Attendee;
use Illuminate\Console\Command;

class AssignAttendeesToUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-attendees-to-user-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Assigning attendees to user...');

        $attendees = Attendee::all();

        $bar = $this->output->createProgressBar($attendees->count());

        $attendees->each(function ($attendee) use ($bar) {
            $event = $attendee->event;
            if ($event) {
                $user = $event->user;
                $this->info('Assigning '.$attendee->id.' to user...'.$user->full_name);
                $attendee->update(['user_id' => $user->id]);
            }

            $bar->advance();
        });

        $bar->finish();
    }
}
