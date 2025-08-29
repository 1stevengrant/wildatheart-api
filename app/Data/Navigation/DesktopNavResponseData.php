<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DesktopNavResponseData extends Data
{
    /**
     * @param  DesktopNavItemData[]  $items
     */
    public function __construct(
        #[DataCollectionOf(DesktopNavItemData::class)]
        public DataCollection $items,
    ) {}

    /**
     * Create from array of Statamic navigation items
     */
    public static function fromStatamicData(array $items): self
    {
        $collection = DesktopNavItemData::collection(
            collect($items)
                ->filter(fn ($item) => $item['enabled'] ?? false)
                ->map(fn ($item) => DesktopNavItemData::fromStatamic($item)->withEnabledDropdownLinks())
                ->values()
                ->toArray()
        );

        return new self(items: $collection);
    }

    /**
     * Convert to array for JSON response
     */
    public function toResponseArray(): array
    {
        return $this->items->toArray();
    }
}
