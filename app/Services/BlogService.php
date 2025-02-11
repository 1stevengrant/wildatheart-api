<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Statamic\Eloquent\Entries\Entry;
use Spatie\LaravelData\DataCollection;
use App\DataTypeObjects\API\V1\Content\BlogResponse;

class BlogService
{
    // Keep original constants for backward compatibility
    public const STASI = 'Stasi Eldredge';

    public const JOHN = 'John Eldredge';

    public const CRAIG = 'Craig McConnell';

    public const MORGAN = 'Morgan Snyder';

    public const BART = 'Bart Hansen';

    public const UNKNOWN_AUTHOR = 'Unknown Author';

    private function getEntryQuery()
    {
        return Entry::query()->where('collection', 'blogs');
    }

    private function getAuthorsQuery()
    {
        return Entry::query()->where('collection', 'authors');
    }

    public function createBlogResponse(Entry $entry): BlogResponse
    {
        return BlogResponse::from([
            ...$entry->toArray(),
            'summary' => $this->generateSummary($entry->get('content')),
            'author_name' => $entry->author ? $entry->author->title : null,
            'published_date' => $entry->date(),
            'featured_image_src' => $entry->get('main_image'),
            'api_url' => config('app.url').'/api/content/v1'.$entry->uri(),
        ]);
    }

    public function handleSingleSegmentUri(string $slug): BlogResponse|JsonResponse|DataCollection
    {
        // Try to find author blogs first
        $author = $this->findAuthorBySlug($slug);
        if ($author) {
            return $this->getBlogsByAuthor($author);
        }

        // If no author found, try to find single blog
        $entry = $this->getEntryQuery()
            ->where('uri', "/blogs/$slug")
            ->first();

        return $entry
            ? $this->createBlogResponse($entry)
            : $this->notFoundResponse();
    }

    public function handleDoubleSegmentUri(string $uri): BlogResponse|JsonResponse
    {
        $entry = $this->getEntryQuery()
            ->where('uri', "/blogs/$uri")
            ->first();

        return $entry
            ? $this->createBlogResponse($entry)
            : $this->notFoundResponse();
    }

    public function findAuthorBySlug(string $slug): ?Entry
    {
        return $this->getAuthorsQuery()
            ->get()
            ->first(fn ($entry) => explode('-', $entry->slug)[0] === $slug);
    }

    public function getBlogsByAuthor(Entry $author): DataCollection
    {
        $entries = $this->getEntryQuery()
            ->where('author', $author->id())
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($entry) => $this->createBlogResponse($entry));

        return BlogResponse::collection($entries);
    }

    public function notFoundResponse(): JsonResponse
    {
        return response()->json(['message' => 'Entry not found'], 404);
    }

    public function showByAuthor(string $authorSlug): JsonResponse|DataCollection
    {
        $author = $this->findAuthorBySlug($authorSlug);

        if (! $author) {
            return $this->notFoundResponse();
        }

        return $this->getBlogsByAuthor($author);
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
