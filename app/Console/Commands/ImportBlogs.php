<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;

class ImportBlogs extends Command
{
    protected $signature = 'app:import-blogs';

    protected $description = 'Command description';

    public const UNKNOWN_AUTHOR = 'Unknown Author';

    public function handle()
    {
        $blogs = collect(Http::get('https://wildatheart.org/json/blogs')->json())
            ->toArray();

        $this->output->progressStart(count($blogs));

        collect($blogs)
            ->each(function ($blog) {

                $this->info('Importing '.$blog['title']);
                $this->line('');
                $title = strip_tags($blog['title']);

                match (true) {
                    str_contains($blog['slug'], 'stasi') => $author = 'Stasi Eldredge',
                    str_contains($blog['slug'], 'john') => $author = 'John Eldredge',
                    str_contains($blog['slug'], 'craig') => $author = 'Craig McConnell',
                    str_contains($blog['slug'], 'morgan') => $author = 'Morgan Snyder',
                    str_contains($blog['slug'], 'bart') => $author = 'Bart Hansen',
                    default => $author = self::UNKNOWN_AUTHOR,
                };

                ray([
                    $author, $title,
                ]);

                $authorEntry = Entry::query()
                    ->where('collection', 'authors')
                    ->where('title', $author)
                    ->first();

                $newData = [
                    'title' => $title,
                    'content' => Purify::clean($blog['body']),
                    'author' => $authorEntry?->id() ?? null,
                    'drupal_id' => $blog['_id'],
                    'drupal_url' => str_replace('https://wildatheart.org', '', $blog['slug']),
                    'drupal_type' => $blog['_type'],
                ];

                $entry = Entry::make()
                    ->collection('blogs')
                    ->slug($title)
                    ->data($newData)
                    ->published($this->determinePublishedStatus($author));
                $publishedAtDate = Carbon::parse($blog['publishedAt']);
                $entry->date($publishedAtDate);
                $entry->save();

                $this->output->progressAdvance();
            });

        $this->output->progressFinish();
    }

    public function determinePublishedStatus($author): bool
    {
        if ($author === self::UNKNOWN_AUTHOR || $author === 'Morgan Snyder') {
            return false;
        }

        return true;
    }
}
