<?php

namespace App\Services;

use Carbon\Carbon;
use Statamic\Entries\Entry;
use App\DataTypeObjects\IconProps;
use App\Builders\EntryQueryBuilder;
use App\DataTypeObjects\SlideBlock;
use Statamic\Eloquent\Assets\Asset;
use App\DataTypeObjects\ButtonProps;
use App\DataTypeObjects\CardVariant;
use App\DataTypeObjects\HeroTitleBlock;
use App\DataTypeObjects\MediaItemsSlide;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashMediaSeriesImporter;

class BlockBuilderService
{
    public function __construct(
        private PreviewBuilderService $previewBuilder,
        private SubsplashApiClient $apiClient,
        private SubsplashMediaSeriesImporter $seriesImporter,
        private BunnyCDNService $bunnyCDN,
    ) {}

    public function build(array $blocks)
    {
        $output = [];

        foreach ($blocks as &$block) {
            $inner = $block;

            if (($block['type'] ?? null) === 'set' && isset($block['attrs']['values'])) {
                $inner = $block['attrs']['values'];
            }

            $result = match ($inner['type']) {
                'media_slider' => $this->processMediaSliderBlock($inner),
                'hero_title' => $this->processHeroTitleBlock($inner),
                default => $this->processDefaultBlock($inner)
            };

            $output[] = $result instanceof \Spatie\LaravelData\Data ? $result->toArray() : $result;
        }

        return $output;
    }

    public function processDefaultBlock($block)
    {
        // Serialize then decode to resolve all Statamic Value/Values objects,
        // then normalize link fields back to simple URL strings.
        $array = json_decode(json_encode($block), true);

        return $this->normalizeLinks($array);
    }

    private function normalizeLinks(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        // Statamic v6 link fields serialize to either:
        // - Simple/null link: ['url' => '/path'] or ['url' => null]
        // - Entry reference: ['id', 'title', 'url', 'permalink', ...]
        // Flatten to just the URL string (or null) for v5 API compatibility.
        if (array_key_exists('url', $data)) {
            if (count($data) === 1) {
                return $data['url'];
            }

            if (isset($data['permalink']) && count($data) <= 6) {
                return $data['url'];
            }
        }

        return array_map(fn ($item) => $this->normalizeLinks($item), $data);
    }

    public function processHeroTitleBlock($block)
    {
        $heroTitle = $block['hero_title'];

        if ($heroTitle instanceof \Statamic\Fields\Value) {
            $heroTitle = $heroTitle->raw();
        }

        return HeroTitleBlock::from([
            'hero_title' => $heroTitle,
        ]);
    }

    public function processMediaSliderBlock($block)
    {
        $processedBlock = $this->normalizeLinks(
            json_decode(json_encode($block), true)
        );

        $processedBlock['slides'] = [];

        foreach ($block->slides as $slide) {
            if ($slide->type === 'subsplash_list_id') {
                $subsplashSlides = $this->buildSubsplashSlide($slide);
                $processedBlock['slides'] = array_merge($processedBlock['slides'], $subsplashSlides->slides);
            } elseif ($slide->type === 'media_items_from_series') {
                $mediaItemSlides = $this->buildMediaItemsFromSeriesSlide($slide);
                $processedBlock['slides'] = array_merge($processedBlock['slides'], $mediaItemSlides->slides);
            } elseif ($slide->type === 'custom_slide') {
                $processedBlock['slides'][] = $this->buildCustomSlide($slide);
            } elseif ($slide->type === 'content_in_taxonomy') {
                $recentlyAddedSlide = $this->buildContentInTaxonomySlide($slide);
                $processedBlock['slides'] = array_merge($processedBlock['slides'], $recentlyAddedSlide->slides);
            } elseif ($slide->type === 'books') {
                $booksSlide = $this->buildBooksSlide($slide);
                $processedBlock['slides'] = array_merge($processedBlock['slides'], $booksSlide->slides);
            } else {
                $processedBlock['slides'][] = $slide;
            }
        }

        return $processedBlock;
    }

    public function buildCustomSlide($slide)
    {
        return SlideBlock::from([
            'tag' => $slide->tag,
            'title' => $slide->title,
            'featured_image_src' => $this->imageFromCDN($slide->featured_image),
            'description' => $slide->description,
            'details' => $slide->details,
            'variant' => CardVariant::portrait,
            'trim_description_bool' => $slide->trim_description_bool,
            'href' => $slide->uri,
            'right_cta' => $this->buildButtonProps($slide->right_cta),
            'left_cta' => $this->buildButtonProps($slide->left_cta),
        ]);
    }

    public function buildMediaItemsFromSeriesSlide(&$slide)
    {
        $mediaItemsFromSeriesSlide = new MediaItemsSlide;
        if (! isset($slide->media_items_from_series->id)) {
            return $mediaItemsFromSeriesSlide;
        }

        $seriesId = $slide->media_items_from_series->id;
        $statamicSeries = $this->seriesImporter->findInStatamic($seriesId);

        $mediaItemsFromSeries = EntryQueryBuilder::mediaItems()
            ->published()
            ->inSeries($statamicSeries->id)
            ->oldestFirst()
            ->get();

        foreach ($mediaItemsFromSeries as $mediaItem) {
            $preview = $this->previewBuilder->build($mediaItem);
            $slide = SlideBlock::from($preview);

            $slide->variant = CardVariant::portrait;
            $slide->trim_description_bool = false;

            if ($mediaItem->blueprint->handle === 'subsplash_media_item') {
                $slide->trim_description_bool = true;
            }

            $slide->href = $mediaItem->date > now() && ! $mediaItem->make_scheduled_page_public ? null : $mediaItem->uri;
            $slide->is_coming_soon = $mediaItem->date > now() && ! $mediaItem->make_scheduled_page_public;
            $slide->date = $mediaItem->date;
            $slide->right_cta = ButtonProps::from([
                'text' => "Play {$mediaItem->media_type}",
                'href' => $mediaItem->date > now() ? null : $mediaItem->uri,
                'variant' => 'cta',
                'leftIcon' => IconProps::from([
                    'name' => 'play',
                ]),
                'rightIcon' => IconProps::from([]),
            ]);

            if ($mediaItem->collection->handle === 'media_item') {
                $slide->tags = [Carbon::parse($mediaItem->date)->format('F d, Y')];
            }

            $slide->tag = $mediaItem->position >= 1 ? "{$mediaItem->media_type} {$mediaItem->position}" : $mediaItem->media_type;

            $mediaItemsFromSeriesSlide->slides[] = $slide;
        }

        return $mediaItemsFromSeriesSlide;
    }

    public function buildSubsplashSlide(&$slide): MediaItemsSlide
    {
        $subsplashListSlide = new MediaItemsSlide;
        $subsplashList = $this->apiClient->getListRows($slide->subsplash_list_id);
        foreach ($subsplashList['_embedded']['list-rows'] as $item) {
            $series = $item['_embedded']['media-series'];
            $images = $series['_embedded']['images'];
            $wideImage = collect($images)->firstWhere('type', 'wide');
            $wideImageUrl = data_get($wideImage, '_embedded.source._links.related.href');

            $entry = $this->seriesImporter->findInStatamic($series['id']);
            // Need to import series if it doesn't exist
            if (! $entry) {
                continue;
            }

            $slide = SlideBlock::from([
                'id' => $series['id'],
                'title' => $series['title'],
                'featured_image_src' => $wideImageUrl,
                'description' => $series['subtitle'],
                'variant' => CardVariant::portrait,
                'href' => $entry->uri,
            ]);

            $subsplashListSlide->slides[] = $slide;
        }

        return $subsplashListSlide;
    }

    public function buildContentInTaxonomySlide($slide)
    {
        $recentlyAddedSlide = new MediaItemsSlide;

        $query = EntryQueryBuilder::query()
            ->published()
            ->pastPublishDate()
            ->where('collection', $slide->collections_to_load_from)
            ->recentFirst();

        // Add taxonomy terms filter if they exist
        if (! empty($slide->tags->toArray())) {
            $query->whereTaxonomyIn($slide->tags->pluck('id'));
        }

        $recentItems = $query->limit($slide->limit ?? 10)->get();

        foreach ($recentItems as $item) {
            $recentlyAddedSlide->slides[] = $this->createSlideFromEntry($item);
        }

        return $recentlyAddedSlide;
    }

    public function createSlideFromEntry($entry)
    {
        $slide = SlideBlock::from();

        $preview = $this->previewBuilder->build($entry);

        $slide->id = $preview->id;
        $slide->title = $preview->title;
        $slide->featured_image_src = $preview->featured_image_src;
        $slide->description = $preview->description;
        $slide->href = $preview->uri;
        $slide->date = $preview->published_at;
        if ($preview->related_content) {
            $slide->leader = $preview->related_content[0]->title;
        }

        $slide->trim_description_bool = true;
        if ($entry->collection->handle === 'books') {
            $slide->variant = CardVariant::book;
            $slide->details = implode(', ', $entry->authors->pluck('author_name')->toArray());
            if (! empty($entry->subtitle)) {
                $slide->subtitle = $entry->subtitle;
            }
        }

        if ($entry->collection->handle === 'blogs') {
            return $preview;
        }

        return $slide;
    }

    private function buildButtonProps($cta): ButtonProps
    {
        return ButtonProps::from([
            'text' => $cta->text,
            'href' => $cta->href,
            'variant' => $cta->variant,
            'leftIcon' => IconProps::from([
                'name' => $cta->left_icon->icon,
                'width' => $cta->left_icon->width,
                'height' => $cta->left_icon->height,
            ]),
            'rightIcon' => IconProps::from([
                'name' => $cta->right_icon->icon,
                'width' => $cta->right_icon->width,
                'height' => $cta->right_icon->height,
            ]),
            'openInModal' => $cta->open_in_modal,
            'openInNewTab' => $cta->open_in_new_tab,
        ]);
    }

    public function imageFromCDN(?Asset $image)
    {
        if (! $image) {
            return null;
        }

        return $this->bunnyCDN->getBunnyCDNUrl($image);
    }

    public function buildBooksSlide($slide)
    {
        $booksSlide = new MediaItemsSlide;
        $authorId = $slide->author->id;
        $excludedBookIds = $slide->excluded_books->pluck('id');
        $query = EntryQueryBuilder::query()
            ->published()
            ->pastPublishDate()
            ->inCollection('books')
            ->where('data->authors', 'like', "%{$authorId}%")
            ->whereNotIn('id', $excludedBookIds)
            ->recentFirst();

        $recentItems = $query->limit($slide->limit ?? 10)->get();

        foreach ($recentItems as $item) {
            $booksSlide->slides[] = $this->createSlideFromEntry($item);
        }

        return $booksSlide;
    }
}
