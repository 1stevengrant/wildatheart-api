<?php

namespace App\Http\Controllers\API;

use Statamic\Facades\Entry;
use App\Http\Controllers\Controller;

class BlogsController extends Controller
{
    public function index()
    {
        return cache()->remember('blogs.index', now()->addHour(), function () {
            return Entry::query()
                ->where('collection', 'blogs')
                ->where('published', true)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($entry) {

                    return [
                        'id' => $entry->id(),
                        'title' => $entry->get('title'),
                        'published_date' => $entry->date()->format('F jS, Y'),
                        'author' => Entry::find($entry->get('author'))?->get('title'),
                        'summary' => $this->generateSummary($entry->get('content')),
                        'json_url' => route('content.blogs.show', $entry->id()),
                    ];
                });
        });
    }

    public function show(string $id)
    {
        return cache()->remember("blogs.show.{$id}", now()->addHour(), function () use ($id) {
            $entry = Entry::find($id);

            if (! $entry) {
                return response()->json(['message' => 'Entry not found'], 404);
            }

            return [
                'id' => $entry->id(),
                'title' => $entry->get('title'),
                'published_date' => $entry->date()->format('F jS, Y'),
                'author' => Entry::find($entry->get('author'))?->get('title'),
                'content' => $entry->get('content'),
            ];
        });
    }

    protected function generateSummary(string $content)
    {
        $content = strip_tags($content);
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        $limit = 200;

        if (mb_strlen($content, 'UTF-8') > $limit) {
            $summary = mb_substr($content, 0, $limit, 'UTF-8');
            $lastSpace = mb_strrpos($summary, ' ', 0, 'UTF-8');
            $summary = $lastSpace !== false ? mb_substr($summary, 0, $lastSpace, 'UTF-8') : $summary;
            $summary .= '...';
        } else {
            $summary = $content;
        }

        return $summary;
    }
}
