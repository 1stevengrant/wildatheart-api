<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Statamic\Eloquent\Entries\Entry;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use App\DataTypeObjects\API\V1\Content\BlogResponse;
use Spatie\LaravelData\CursorPaginatedDataCollection;

class BlogService
{
    public const STASI = 'Stasi Eldredge';

    public const JOHN = 'John Eldredge';

    public const CRAIG = 'Craig McConnell';

    public const MORGAN = 'Morgan Snyder';

    public const BART = 'Bart Hansen';

    public const UNKNOWN_AUTHOR = 'Unknown Author';

    public function createBlogResponse(Entry $entry): BlogResponse
    {
        return BlogResponse::from([
            ...$entry->toArray(),
            'summary' => $this->generateSummary($entry->get('content')),
            'author_name' => $entry->author ? $entry->author->title : null,
            'published_date' => $entry->date(),
        ]);
    }

    public function handleSingleSegmentUri(string $slug): BlogResponse|JsonResponse|DataCollection|CursorPaginatedDataCollection|PaginatedDataCollection
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

    public function handleDoubleSegmentUri(string $uri): BlogResponse|JsonResponse
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
            ->orderBy('date', 'desc')
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

    private function generateSummary(string|array $content): string
    {
        $plainText = $this->extractPlainText($content);

        return $this->createSummary($plainText);
    }

    private function extractPlainText(string|array $content): string
    {
        if (is_string($content)) {
            return strip_tags($content);
        }

        $text = [];
        foreach ($content as $block) {
            if ($block['type'] === 'paragraph' && isset($block['content'])) {
                foreach ($block['content'] as $textBlock) {
                    if (isset($textBlock['text'])) {
                        $text[] = $textBlock['text'];
                    }
                }
            }
        }

        return implode(' ', $text);
    }

    private function createSummary(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $limit = 200;

        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }

        $summary = mb_substr($text, 0, $limit, 'UTF-8');
        $lastSpace = mb_strrpos($summary, ' ', 0, 'UTF-8');

        return $lastSpace !== false
            ? mb_substr($summary, 0, $lastSpace, 'UTF-8').'...'
            : $summary.'...';
    }
}
