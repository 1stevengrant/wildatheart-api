<?php

namespace App\Services;

use Statamic\Entries\Entry;
use App\DataTypeObjects\IconProps;
use App\DataTypeObjects\SlideBlock;
use Statamic\Eloquent\Assets\Asset;
use App\DataTypeObjects\ButtonProps;
use App\DataTypeObjects\CardVariant;
use App\DataTypeObjects\HeroTitleBlock;
use App\DataTypeObjects\MediaItemsSlide;

class BlockBuilderService
{
    private PreviewBuilderService $previewBuilder;

    public function __construct(PreviewBuilderService $previewBuilder)
    {
        $this->previewBuilder = $previewBuilder;
    }

    public function build(array $blocks)
    {

        $output = [];
        foreach ($blocks as &$block) {

            $output[] = match ($block['type']) {
                'media_slider' => $this->processMediaSliderBlock($block),
                'hero_title' => $this->processHeroTitleBlock($block),
                default => $this->processDefaultBlock($block)
            };
        }

        return $output;
    }

    public function processDefaultBlock($block)
    {
        return $block;
    }

    public function blocksBuilder($blocks)
    {

        $output = [];
        foreach ($blocks as &$block) {

            $output[] = match ($block['type']) {
                'media_slider' => $this->processMediaSliderBlock($block),
                default => $this->processDefaultBlock($block)
            };
        }

        return $output;
    }

    public function processHeroTitleBlock($block)
    {
        return HeroTitleBlock::from([
            'hero_title' => $block['hero_title'],
            'text_style' => $block['text_style'],
        ]);
    }

    public function processMediaSliderBlock($block)
    {

        $processedBlock = $block->toArray();

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
            'right_cta' => ButtonProps::from([
                'text' => $slide->right_cta->text,
                'href' => $slide->right_cta->href,
                'variant' => $slide->right_cta->variant,
                'leftIcon' => IconProps::from([
                    'name' => $slide->right_cta->left_icon->icon,
                    'width' => $slide->right_cta->left_icon->width,
                    'height' => $slide->right_cta->left_icon->height,
                ]),
                'rightIcon' => IconProps::from([
                    'name' => $slide->right_cta->right_icon->icon,
                    'width' => $slide->right_cta->right_icon->width,
                    'height' => $slide->right_cta->right_icon->height,
                ]),
                'openInModal' => $slide->right_cta->open_in_modal,
                'openInNewTab' => $slide->right_cta->open_in_new_tab,
            ]),
            'left_cta' => ButtonProps::from([
                'text' => $slide->left_cta->text,
                'href' => $slide->left_cta->href,
                'variant' => $slide->left_cta->variant,
                'leftIcon' => IconProps::from([
                    'name' => $slide->left_cta->left_icon->icon,
                    'width' => $slide->left_cta->left_icon->width,
                    'height' => $slide->left_cta->left_icon->height,
                ]),
                'rightIcon' => IconProps::from([
                    'name' => $slide->left_cta->right_icon->icon,
                    'width' => $slide->left_cta->right_icon->width,
                    'height' => $slide->left_cta->right_icon->height,
                ]),
                'openInModal' => $slide->left_cta->open_in_modal,
                'openInNewTab' => $slide->left_cta->open_in_new_tab,
            ]),
        ]);

        return $slide;
    }

    public function buildMediaItemsFromSeriesSlide(&$slide)
    {
        $mediaItemsFromSeriesSlide = new MediaItemsSlide;
        if (! isset($slide->media_items_from_series->id)) {
            return $mediaItemsFromSeriesSlide;
        }

        $seriesId = $slide->media_items_from_series->id;

        $mediaItemsFromSeries = Entry::query()
            ->where('collection', 'media_item')
            ->where('published', true)
            ->where('data->series', 'like', "%{$seriesId}")
            ->orderBy('date', 'asc')
            ->get();

        foreach ($mediaItemsFromSeries as $mediaItem) {
            $mediaItemsFromSeriesSlide->slides[] = SlideBlock::from([
                'id' => $mediaItem->id,
                'tag' => $mediaItem->position >= 1 ? "{$mediaItem->media_type} {$mediaItem->position}" : $mediaItem->media_type,
                'title' => $mediaItem->title,
                'featured_image_src' => $this->imageFromCDN($mediaItem->images->wide),
                'description' => $mediaItem->description,
                'variant' => CardVariant::portrait,
                'trim_description_bool' => false,
                'href' => $mediaItem->date > now() && ! $mediaItem->make_scheduled_page_public ? null : $mediaItem->uri,
                'is_coming_soon' => $mediaItem->date > now() && ! $mediaItem->make_scheduled_page_public,
                'date' => $mediaItem->date,
                'right_cta' => ButtonProps::from([
                    'text' => "Play {$mediaItem->media_type}",
                    'href' => $mediaItem->date > now() ? null : $mediaItem->uri,
                    'variant' => 'cta',
                    'leftIcon' => IconProps::from([
                        'name' => 'play',
                    ]),
                    'rightIcon' => IconProps::from([]),
                ]),
            ]);
        }

        return $mediaItemsFromSeriesSlide;
    }

    public function buildSubsplashSlide(&$slide): MediaItemsSlide
    {
        $subsplashListSlide = new MediaItemsSlide;
        $subsplash = new SubsplashService;
        $url = 'https://core.subsplash.com/builder/v1/list-rows?filter%5Bsource_list%5D=' . $slide->subsplash_list_id;
        $subsplashList = $subsplash->subsplashFetch($url);

        foreach ($subsplashList['_embedded']['list-rows'] as $item) {
            $series = $item['_embedded']['media-series'];
            $images = $series['_embedded']['images'];
            $wideImage = collect($images)->firstWhere('type', 'wide');
            $wideImageUrl = data_get($wideImage, '_embedded.source._links.related.href');

            $entry = Entry::find($series['id']);

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

        $collectionsToLoadFrom = $slide->collections_to_load_from->pluck('handle');

        $query = Entry::query()
            ->where('published', true)
            ->where('date', '<=', now())
            ->where('collection', $slide->collections_to_load_from)
            ->orderBy('date', 'desc');

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
            $slide->variant = CardVariant::blog;
            $slide->leader = "Article";
            $slide->details = $entry->author->author_name;
        }

        return $slide;
    }

    public function imageFromCDN(?Asset $image)
    {
        if (! $image) {
            return null;
        }

        $cdnUrl = 'https://b-cdn.wildatheart.org/';

        return $cdnUrl . $image->path;
    }

    public function buildBooksSlide($slide)
    {
        $booksSlide = new MediaItemsSlide;
        $authorId = $slide->author->id;
        $excludedBookIds = $slide->excluded_books->pluck('id');
        $query = Entry::query()
            ->where('published', true)
            ->where('date', '<=', now())
            ->where('collection', 'books')
            ->where('data->authors', 'like', "%{$authorId}%")
            ->whereNotIn('id', $excludedBookIds)
            ->orderBy('date', 'desc');

        $recentItems = $query->limit($slide->limit ?? 10)->get();

        foreach ($recentItems as $item) {
            $booksSlide->slides[] = $this->createSlideFromEntry($item);
        }

        return $booksSlide;
    }
}
