<?php

namespace App\Data;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class FeaturedContentData extends Data
{
    public function __construct(
        /** @var DataCollection<int, FeaturedContentItemData> */
        public DataCollection $items,
    ) {}

    public static function fromStatamicData($data): ?self
    {
        $featuredItems = $data->get('featured_content_items', []);

        if (empty($featuredItems)) {
            return null;
        }

        $items = collect($featuredItems)
            ->filter(fn($item) => $item['enabled'] ?? false)
            ->map(function ($item) {
                $event = null;
                $latitude = null;
                $longitude = null;

                if (!empty($item['relates_to'])) {
                    $event = Event::find($item['relates_to']);
                    if ($event) {
                        $latitude = $event->latitude;
                        $longitude = $event->longitude;
                    }
                }

                return new FeaturedContentItemData(
                    id: $item['id'] ?? null,
                    item_text: $item['item_text'] ?? null,
                    item_url: $item['item_url'] ?? null,
                    item_image: $item['item_image'] ?? null,
                    show_in_regions: $item['show_in_regions'] ?? [],
                    relates_to_event_id: $item['relates_to'] ?? null,
                    event_latitude: $latitude,
                    event_longitude: $longitude,
                );
            })
            ->values();

        return new self(
            items: FeaturedContentItemData::collection($items),
        );
    }
}
