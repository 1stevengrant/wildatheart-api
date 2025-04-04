<?php

namespace App\Console\Commands\User;

use Illuminate\Console\Command;

class UpdateUserPhoneNumbersCommand extends Command
{
    protected $signature = 'app:update-user-phone-numbers';

    protected $description = 'update the user phone numbers from auth0';

    public function handle(): void
    {
        $usersWithoutPhone = \App\Models\User::whereNull('phone')->whereNotNull('provider_id')->get();

        $bar = $this->output->createProgressBar($usersWithoutPhone->count());

        $bar->start();

        foreach ($usersWithoutPhone as $user) {
            \App\Jobs\User\UpdateProfileFromAuth0Job::dispatch($user);
            $bar->advance();
        }

        $bar->finish();
    }
}
