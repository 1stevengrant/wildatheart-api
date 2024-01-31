<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupEventTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-event-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): void
    {
        $this->call('db:seed', ['--class' => 'EventTypeSeeder']);
        //        $this->call('app:import-from-drupal');
        $this->call('scout:import "App\Models\Event"');
    }
}
