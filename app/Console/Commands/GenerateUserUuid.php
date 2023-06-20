<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateUserUuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-user-uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a UUID for the user';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Generating user UUIDs...');

        $users = \App\Models\User::query()
            ->whereNull('uuid')
            ->get();

        $bar = $this->output->createProgressBar(count($users));

        foreach ($users as $user) {
            $user->update(['uuid' => \Illuminate\Support\Str::uuid()]);
            $bar->advance();
        }

        $bar->finish();

        $this->info('User UUIDs generated!');
    }
}
