<?php

namespace App\Services;

use App\DataTypeObjects\ContentPreview;
use Statamic\Eloquent\Entries\Entry;

/**
 * Service for building preview data for various blueprint and entry types in Statamic
 */
class PreviewBuilderService
{
  private $bunnyCDN;
  private $subsplash;

  public function __construct()
  {
    $this->bunnyCDN = new BunnyCDNService;
    $this->subsplash = new SubsplashService;
  }

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
            case 'custom_media_item':
                return $this->buildCustomMediaItemPreview($entry);
        }
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
}
