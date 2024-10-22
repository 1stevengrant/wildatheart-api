<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateSalesforceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-salesforce-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = collect(Http::get('https://wildatheart.org/json/allies')->json());

        $this->output->progressStart(count($users));

        $users->each(function ($user) {

            $existingUser = User::whereEmail($user['email'])->first();
            if ($existingUser) {
                $this->info('Updating '.$user['email']);
                $this->line('');
                $existingUser->update([
                    'salesforce_contact_id' => $user['sf_contact_id'],
                ]);
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
    }
}
