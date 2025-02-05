<?php

namespace App\Http\Controllers\API;

use App\DataTypeObjects\API\V1\Content\BlogResponse;
use Statamic\Facades\Entry;
use App\Http\Controllers\Controller;
use Statamic\Entries\Entry as StatamicEntry;

class BlogsController extends Controller
{
    public const STASI = 'Stasi Eldredge';

    public const JOHN = 'John Eldredge';

    public const CRAIG = 'Craig McConnell';

    public const MORGAN = 'Morgan Snyder';

    public const BART = 'Bart Hansen';

    public const UNKNOWN_AUTHOR = 'Unknown Author';

    public function index()
    {
        return cache()->remember('blogs.index', now()->addHour(), function () {
            return Entry::query()
                ->where('collection', 'blogs')
                ->where('published', true)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($entry) {
                    return $this->createBlogResponse($entry);
                });
        });
    }

    public function show(string $uri)
    {
        return cache()->remember("blogs.show.{$uri}", now()->addHour(), function () use ($uri) {
            $uriParts = explode('/', $uri);

            if (count($uriParts) === 1) {
                return $this->handleSingleSegmentUri($uriParts[0]);
            }

            return $this->handleDoubleSegmentUri($uri);
        });
    }

    protected function createBlogResponse(StatamicEntry $entry)
    {
        return BlogResponse::from([
            ...$entry->toArray(),
            'summary' => $this->generateSummary($entry->get('content')),
            'author_name' => $entry->author ? $entry->author->title : null,
            'published_date' => $entry->date(),
        ]);
    }

    protected function handleSingleSegmentUri(string $slug)
    {
        // Try to find author blogs first
        $author = $this->findAuthorBySlug($slug);
        if ($author) {
            return $this->getBlogsByAuthor($author);
        }

        // If no author found, try to find single blog
        $entry = Entry::query()
            ->where('collection', 'blogs')
            ->where('uri', "/blogs/$slug")
            ->first();

        if (! $entry) {
            return $this->notFoundResponse();
        }

        return $this->createBlogResponse($entry);
    }

    protected function handleDoubleSegmentUri(string $uri)
    {
        $entry = Entry::query()
            ->where('collection', 'blogs')
            ->where('uri', "/blogs/$uri")
            ->first();

        if (! $entry) {
            return $this->notFoundResponse();
        }

        return $this->createBlogResponse($entry);
    }

    protected function findAuthorBySlug(string $slug)
    {
        return Entry::query()
            ->where('collection', 'authors')
            ->get()
            ->first(function ($entry) use ($slug) {
                $slugParts = explode('-', $entry->slug);
                return $slugParts[0] === $slug;
            });
    }


    protected function getBlogsByAuthor($author)
    {
        $entries = Entry::query()
            ->where('collection', 'blogs')
            ->where('author', $author->id())
            ->get()
            ->map(function ($entry) {
                return $this->createBlogResponse($entry);
            });

        return BlogResponse::collection($entries);
    }

    protected function notFoundResponse()
    {
        return response()->json(['message' => 'Entry not found'], 404);
    }

    public function showByAuthor(string $authorSlug)
    {
        // return cache()->remember("blogs.show.{$id}", now()->addHour(), function () use ($id) {
        $author = Entry::query()
            ->where('collection', 'authors')
            ->get()
            ->first(function ($entry) use ($authorSlug) {
                $slugParts = explode('-', $entry->slug);
                return $slugParts[0] === $authorSlug;
            });

        $entries = Entry::query()
            ->where('collection', 'blogs')
            ->where('author', $author->id())
            ->get()
            ->map(function ($entry) {
                return [
                    ...$entry->toArray(),
                    'summary' => $this->generateSummary($entry->get('content')),
                ];
            });

        if (! $entries) {
            return response()->json(['message' => 'Entry not found'], 404);
        }

        $response = BlogResponse::collection($entries);

        return $response;
        // });
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

    protected function generateUrl(?string $author)
    {
        match ($author) {
            self::STASI => $url = '/blog/stasi',
            self::JOHN => $url = '/blog/john',
            self::CRAIG => $url = '/blog/craig',
            self::MORGAN => $url = '/blog/morgan',
            self::BART => $url = '/blog/bart',
            default => $url = '/blog',
        };

        return $url . '/';
    }
}
