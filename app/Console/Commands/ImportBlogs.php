<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use App\Builders\EntryQueryBuilder;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;

class ImportBlogs extends Command
{
    protected $signature = 'app:import-blogs';

    protected $description = 'Import the blogs from Drupal';

    public const STASI = 'Stasi Eldredge';

    public const JOHN = 'John Eldredge';

    public const CRAIG = 'Craig McConnell';

    public const MORGAN = 'Morgan Snyder';

    public const BART = 'Bart Hansen';

    public const UNKNOWN_AUTHOR = 'Unknown Author';

    public function handle()
    {
        $blogs = collect(Http::get('https://wildatheart.org/json/blogs')->json())
            ->toArray();

        $this->output->progressStart(count($blogs));

        collect($blogs)
            ->each(function ($blog) {

                $bart = EntryQueryBuilder::authors()
                    ->where('title', $this::BART)
                    ->first();
                if (! $bart) {

                    $this->info('Creating contributor page for Bart');
                    $bartData = [
                        'title' => $this::BART,
                    ];

                    Entry::make()
                        ->collection('authors')
                        ->slug($this::BART)
                        ->data($bartData)
                        ->date(now())
                        ->published(true)
                        ->save();
                }

                $this->info('Importing ' . $blog['title']);
                $this->line('');
                $title = strip_tags($blog['title']);

                match (true) {
                    str_contains($blog['slug'], 'stasi') => $author = $this::STASI,
                    str_contains($blog['slug'], 'john') => $author = $this::JOHN,
                    str_contains($blog['slug'], 'craig') => $author = $this::CRAIG,
                    str_contains($blog['slug'], 'morgan') => $author = $this::MORGAN,
                    str_contains($blog['slug'], 'bart') => $author = $this::BART,
                    default => $author = $this::UNKNOWN_AUTHOR,
                };

                if ($author === $this::MORGAN) {
                    $this->info('Skipping ' . $blog['title']);

                    return;
                }
                ray([
                    $author,
                    $title,
                ]);

                $authorEntry = EntryQueryBuilder::authors()
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
        if ($author === $this::UNKNOWN_AUTHOR || $author === $this::MORGAN) {
            return false;
        }

        return true;
    }
}
