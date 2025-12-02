<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DesktopNavResponseData extends Data
{
    public function __construct(
        /** @var DesktopNavItemData[] */
        public array $items,
    ) {}

    /**
     * Create from array of Statamic navigation items
     */
    public static function fromStatamicData(array $items): self
    {
        $itemsData = collect($items)
            ->filter(fn ($item) => $item['enabled'] ?? false)
            ->map(fn ($item) => DesktopNavItemData::fromStatamic($item)->withEnabledDropdownLinks())
            ->values()
            ->toArray();

        return new self(items: $itemsData);
    }

    /**
     * Convert to array for JSON response
     */
    public function toResponseArray(): array
    {
        return array_map(
            fn ($item) => $item instanceof DesktopNavItemData ? $item->toArray() : $item,
            $this->items
        );
    }
}
