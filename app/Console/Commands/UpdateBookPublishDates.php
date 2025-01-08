<?php

namespace App\Console\Commands;

use Statamic\Entries\Entry;
use Illuminate\Console\Command;

class UpdateBookPublishDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-book-publish-dates';

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

        $recentItems = Entry::query()
            ->where('collection', 'books')
            ->where('published', true)
            ->orderBy('date', 'asc')
            ->get();

        foreach ($recentItems as $item) {
            $item->date($item->publication_date);
            $item->save();
        }
    }
}
