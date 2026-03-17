<?php

namespace App\Services;

use Statamic\Support\Str;
use Statamic\Eloquent\Entries\Entry;
use App\DataTypeObjects\ContentPreview;
use App\DataTypeObjects\Previews\BlogPreview;
use App\Services\Subsplash\SubsplashApiClient;

/**
 * Service for building preview data for various blueprint and entry types in Statamic
 */
class PreviewBuilderService
{
    public function __construct(
        private BunnyCDNService $bunnyCDN,
        private SubsplashApiClient $apiClient,
    ) {}

    public function build(Entry $entry)
    {
        return match ($entry->blueprint->handle) {
            'podcast' => $this->buildPodcastPreview($entry),
            'subsplash_media_series' => $this->buildSubsplashMediaSeriesPreview($entry),
            'subsplash_media_item' => $this->buildSubsplashMediaItemPreview($entry),
            'experience_session' => $this->buildExperienceSessionPreview($entry),
            'book' => $this->buildBookPreview($entry),
            'custom_media_item' => $this->buildCustomMediaItemPreview($entry),
            'blog' => $this->buildBlogPreview($entry),
            default => null,
        };
    }

    private function extractWideImageUrl(array $embedded): ?string
    {
        $images = data_get($embedded, 'images', []);
        $wideImage = collect($images)->firstWhere('type', 'wide');

        return data_get($wideImage, '_embedded.source._links.related.href');
    }

    private function buildExperienceSessionPreview(Entry $entry)
    {
        $preview = $this->buildSubsplashMediaItemPreview($entry);

        if (! $preview) {
            return null;
        }

        $preview->title = $entry->title;
        $preview->description = $entry->description;
        $preview->published_at = $entry->date;

        return $preview;
    }

    private function buildSubsplashMediaItemPreview(Entry $entry)
    {
        $preview = new ContentPreview;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;
        $preview->published_at = $entry->date;
        $preview->collection = $entry->collection;
        $preview->blueprint = $entry->blueprint;

        $subsplashMediaItem = $this->apiClient->getMediaItem($entry->subsplash_media_item_id);

        if (! $subsplashMediaItem) {
            return null;
        }
        $preview->blueprint = data_get($subsplashMediaItem, 'blueprint');

        if (! empty($subsplashMediaItem['_embedded'])) {
            $preview->featured_image_src = $this->extractWideImageUrl($subsplashMediaItem['_embedded']);
        }

        $preview->description = data_get($subsplashMediaItem, 'summary');

        return $preview;
    }

    private function buildCustomMediaItemPreview(Entry $entry)
    {
        $preview = new ContentPreview;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;
        if ($entry->images->wide) {
            $preview->featured_image_src = $this->bunnyCDN->getBunnyCDNUrl($entry->images->wide);
        }
        $preview->description = $entry->description;
        $preview->published_at = $entry->date;

        return $preview;
    }

    private function buildPodcastPreview(Entry $entry)
    {
        $preview = new ContentPreview;
        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;
        $preview->published_at = $entry->date;
        $preview->collection = $entry->collection;
        $preview->blueprint = $entry->blueprint;

        $subsplashMediaSeries = $this->apiClient->getMediaSeries($entry->subsplash_series_id);

        if (! empty($subsplashMediaSeries['_embedded'])) {
            $preview->featured_image_src = $this->extractWideImageUrl($subsplashMediaSeries['_embedded']);
        }

        if ($entry->description) {
            $preview->description = strip_tags($entry->description);
        }

        return $preview;
    }

    private function buildSubsplashMediaSeriesPreview(Entry $entry)
    {
        $preview = new ContentPreview;
        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;
        $preview->published_at = $entry->date;
        $preview->collection = $entry->collection;
        $preview->blueprint = $entry->blueprint;

        $subsplashMediaSeries = $this->apiClient->getMediaSeries($entry->subsplash_series_id);

        $preview->description = $subsplashMediaSeries['summary'];

        if (! empty($subsplashMediaSeries['_embedded'])) {
            $preview->featured_image_src = $this->extractWideImageUrl($subsplashMediaSeries['_embedded']);
        }

        if ($entry->description) {
            $preview->description = strip_tags($entry->description);
        }

        return $preview;
    }

    private function buildBookPreview(Entry $entry)
    {
        $preview = new ContentPreview;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;

        if ($entry->images && $entry->images->vertical) {
            $preview->featured_image_src = $this->bunnyCDN->getBunnyCDNUrl($entry->images->vertical);
        }

        $preview->description = $entry->description;
        $preview->published_at = $entry->publication_date;
        $preview->collection = $entry->collection;
        $preview->blueprint = $entry->blueprint;

        return $preview;
    }

    private function buildBlogPreview(Entry $entry): BlogPreview
    {
        $html = null;

        if (is_array($entry->content)) {
            $html = Str::markdown(collect($entry->content)->pluck('text')->implode("\n"));
        } else {
            $html = $entry->content;
        }

        $plainText = strip_tags($html);

        $preview = new BlogPreview;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->description = $plainText;
        $preview->published_at = $entry->date;
        $preview->uri = $entry->uri;
        $preview->collection = $entry->collection;
        $preview->blueprint = $entry->blueprint;
        if ($entry->images && $entry->images->vertical) {
            $preview->featured_image_src = $this->bunnyCDN->getBunnyCDNUrl($entry->images->vertical);
        }

        $author = $entry->augmentedValue('author')->value();
        if ($author) {
            $preview->author_name = $author->title;
            $preview->author_image_src = $this->bunnyCDN->getBunnyCDNUrl($author->image);
        }

        return $preview;
    }
}
