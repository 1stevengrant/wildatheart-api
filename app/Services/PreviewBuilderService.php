<?php

namespace App\Services;

use Statamic\Support\Str;
use Statamic\Eloquent\Entries\Entry;
use App\DataTypeObjects\ContentPreview;
use App\DataTypeObjects\Previews\BlogPreview;

/**
 * Service for building preview data for various blueprint and entry types in Statamic
 */
class PreviewBuilderService
{
    private $bunnyCDN;

    private $subsplash;

    public function __construct(
        BunnyCDNService $bunnyCDN,
        SubsplashService $subsplash
    ) {
        $this->bunnyCDN = $bunnyCDN;
        $this->subsplash = $subsplash;
    }

    public function build(Entry $entry)
    {
        switch ($entry->blueprint->handle) {
            case 'podcast':
                return $this->buildPodcastPreview($entry);
            case 'subsplash_media_series':
                return $this->buildSubsplashMediaSeriesPreview($entry);
            case 'subsplash_media_item':
                return $this->buildSubsplashMediaItemPreview($entry);
            case 'experience_session':
                return $this->buildExperienceSessionPreview($entry);
            case 'book':
                return $this->buildBookPreview($entry);
            case 'custom_media_item':
                return $this->buildCustomMediaItemPreview($entry);
            case 'blog':
                return $this->buildBlogPreview($entry);
        }
    }

    private function buildExperienceSessionPreview(Entry $entry)
    {
        $preview = $this->buildSubsplashMediaItemPreview($entry);
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

        $subsplashMediaItem = $this->subsplash->getMediaItem($entry->subsplash_media_item_id);
        if (! empty($subsplashMediaItem['_embedded'])) {
            $images = $subsplashMediaItem['_embedded']['images'];
            $wideImage = collect($images)->firstWhere('type', 'wide');
            $wideImageUrl = data_get($wideImage, '_embedded.source._links.related.href');
            $preview->featured_image_src = $wideImageUrl;
        }

        $preview->description = data_get($subsplashMediaItem, 'summary');
        // if ($entry->series->value() === null) {
        //     dd($entry);
        // }
        if ($entry !== null && $entry->series !== null) {
            // $preview->related_content = [$this->build($entry->series->value())];
        }

        return $preview;
    }

    private function buildCustomMediaItemPreview(Entry $entry)
    {
        $preview = new ContentPreview;
        $bunnyCDN = new BunnyCDNService;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;
        if ($entry->images->wide) {
            $preview->featured_image_src = $bunnyCDN->getBunnyCDNUrl($entry->images->wide);
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

        $subsplashMediaSeries = $this->subsplash->getMediaSeries($entry->subsplash_series_id);

        if (! empty($subsplashMediaSeries['_embedded'])) {
            $images = $subsplashMediaSeries['_embedded']['images'];
            $wideImage = collect($images)->firstWhere('type', 'wide');
            $wideImageUrl = data_get($wideImage, '_embedded.source._links.related.href');
            $preview->featured_image_src = $wideImageUrl;
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

        $subsplashMediaSeries = $this->subsplash->getMediaSeries($entry->subsplash_series_id);

        $preview->description = $subsplashMediaSeries['summary'];

        if (! empty($subsplashMediaSeries['_embedded'])) {
            $images = $subsplashMediaSeries['_embedded']['images'];
            $wideImage = collect($images)->firstWhere('type', 'wide');
            $wideImageUrl = data_get($wideImage, '_embedded.source._links.related.href');
            $preview->featured_image_src = $wideImageUrl;
        }

        if ($entry->description) {
            $preview->description = strip_tags($entry->description);
        }

        return $preview;
    }

    private function buildBookPreview(Entry $entry)
    {
        $preview = new ContentPreview;
        $bunnyCDN = new BunnyCDNService;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->uri = $entry->uri;

        if ($entry->images && $entry->images->vertical) {
            $preview->featured_image_src = $bunnyCDN->getBunnyCDNUrl($entry->images->vertical);
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
        $bunnyCDN = new BunnyCDNService;

        $preview->id = $entry->id;
        $preview->title = $entry->title;
        $preview->description = $plainText;
        $preview->published_at = $entry->date;
        $preview->uri = $entry->uri;
        $preview->collection = $entry->collection;
        $preview->blueprint = $entry->blueprint;
        if ($entry->images && $entry->images->vertical) {
            $preview->featured_image_src = $bunnyCDN->getBunnyCDNUrl($entry->images->vertical);
        }

        $author = $entry->augmentedValue('author')->value();
        if ($author) {
            $preview->author_name = $author->title;
            $preview->author_image_src = $bunnyCDN->getBunnyCDNUrl($author->image);
        }

        return $preview;
    }
}
