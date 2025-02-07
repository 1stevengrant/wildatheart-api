<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Statamic\Facades\Entry;
use App\Services\BlogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Stevebauman\Purify\Facades\Purify;

class ImportBlogs extends Command
{
    protected $signature = 'app:import-blogs';

    protected $description = 'Import the blogs from Drupal';

    public function handle()
    {
        $blogs = collect(Http::get('https://wildatheart.org/json/blogs')->json())
            ->toArray();

        $this->output->progressStart(count($blogs));

        collect($blogs)
            ->each(function ($blog) {

                $existingEntry = Entry::query()
                    ->where('collection', 'blogs')
                    ->where('drupal_id', $blog['_id'])
                    ->first();

                if (! $existingEntry) {
                    $this->createNewBlogEntry($blog);
                } else {
                    $this->info('Skipping '.$blog['title']);
                }

                $this->output->progressAdvance();
            });

        $this->output->progressFinish();
    }

    public function determinePublishedStatus(string $author): bool
    {
        return ! ($author === BlogService::UNKNOWN_AUTHOR || $author === BlogService::MORGAN);
    }

    public function generateSlug(string $slug): string
    {
        $slugParts = explode('/', $slug);

        return end($slugParts);
    }

    public function createNewBlogEntry(array $blog)
    {
        $this->info('Importing '.$blog['title']);
        $this->line('');
        $title = strip_tags($blog['title']);

        match (true) {
            str_contains($blog['slug'], 'stasi') => $author = BlogService::STASI,
            str_contains($blog['slug'], 'john') => $author = BlogService::JOHN,
            str_contains($blog['slug'], 'craig') => $author = BlogService::CRAIG,
            str_contains($blog['slug'], 'morgan') => $author = BlogService::MORGAN,
            str_contains($blog['slug'], 'bart') => $author = BlogService::BART,
            default => $author = BlogService::UNKNOWN_AUTHOR,
        };

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
            ->slug($this->generateSlug($blog['slug']))
            ->data($newData)
            ->published($this->determinePublishedStatus($author));
        $publishedAtDate = Carbon::parse($blog['publishedAt']);
        $entry->date($publishedAtDate);
        $entry->save();
    }
}
