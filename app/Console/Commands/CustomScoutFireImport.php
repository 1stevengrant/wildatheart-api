<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CustomScoutFireImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:custom-scout-fire-import {model}';

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
        $class = $this->argument('model');

        if (class_exists($class)) {
            $model = new $class;

            $model::with('host')->get()->searchable();

            $this->info("All [{$class}] records have been imported.");
        } else {
            $this->error("Model [{$class}] does not exist!");
        }
    }
}
