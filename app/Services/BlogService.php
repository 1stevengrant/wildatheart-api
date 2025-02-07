<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Statamic\Facades\Entry;
use Statamic\Entries\Entry as StatamicEntry;
use App\DataTypeObjects\API\V1\Content\BlogResponse;

class BlogService
{
    public const STASI = 'Stasi Eldredge';

    public const JOHN = 'John Eldredge';

    public const CRAIG = 'Craig McConnell';

    public const MORGAN = 'Morgan Snyder';

    public const BART = 'Bart Hansen';

    public function createBlogResponse(StatamicEntry $entry): BlogResponse
    {
        return BlogResponse::from([
            ...$entry->toArray(),
            'summary' => $this->generateSummary($entry->get('content')),
            'author_name' => $entry->author ? $entry->author->title : null,
            'published_date' => $entry->date(),
        ]);
    }

    public function handleSingleSegmentUri(string $slug)
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

    public function handleDoubleSegmentUri(string $uri)
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

    public function findAuthorBySlug(string $slug)
    {
        return Entry::query()
            ->where('collection', 'authors')
            ->get()
            ->first(function ($entry) use ($slug) {
                $slugParts = explode('-', $entry->slug);

                return $slugParts[0] === $slug;
            });
    }

    public function getBlogsByAuthor($author): PaginatedDataCollection|CursorPaginatedDataCollection|DataCollection
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

    public function notFoundResponse(): JsonResponse
    {
        return response()->json(['message' => 'Entry not found'], 404);
    }

    public function showByAuthor(string $authorSlug): JsonResponse|DataCollection|CursorPaginatedDataCollection|PaginatedDataCollection
    {
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

        return BlogResponse::collection($entries);
    }

    public function generateSummary(string $content): array|false|string|null
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

    public function generateUrl(?string $author): string
    {
        match ($author) {
            self::STASI => $url = '/blog/stasi',
            self::JOHN => $url = '/blog/john',
            self::CRAIG => $url = '/blog/craig',
            self::MORGAN => $url = '/blog/morgan',
            self::BART => $url = '/blog/bart',
            default => $url = '/blog',
        };

        return $url.'/';
    }
}
