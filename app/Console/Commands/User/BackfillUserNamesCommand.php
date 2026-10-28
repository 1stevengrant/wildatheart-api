<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use App\Jobs\WeGive\BackfillUserNameJob;

class BackfillUserNamesCommand extends Command
{
    protected $signature = 'backfill-user-names';

    protected $description = 'Dispatch jobs to backfill missing user names from WeGive donor profiles';

    public function handle(): int
    {
        $users = User::query()
            ->whereNotNull('wegive_donor_id')
            ->where(function ($query) {
                $query->whereNull('given_name')
                    ->orWhereNull('family_name');
            })
            ->get();

        $this->info("Dispatching {$users->count()} backfill jobs.");

        $users->each(function (User $user) {
            BackfillUserNameJob::dispatch($user);
        });

        $this->comment('All jobs dispatched. Monitor progress in the Cloud Queues dashboard.');

        return self::SUCCESS;
    }
}
