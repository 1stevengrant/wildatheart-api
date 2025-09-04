<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MobileNavResponseData extends Data
{
    public function __construct(
        #[MapOutputName('main_nav_bar')]
        /** @var \App\Data\Navigation\NavItemData[] */
        public array $mainNavBar,

        #[MapOutputName('drawer_items')]
        /** @var \App\Data\Navigation\NavItemData[] */
        public array $drawerItems,
    ) {}

    /**
     * Create from Statamic data arrays
     */
    public static function fromStatamicData(array $mainNavBar, array $drawerItems): self
    {
        $mainNavBarData = collect($mainNavBar)
            ->filter(fn ($item) => $item['enabled'] ?? false)
            ->map(fn ($item) => NavItemData::fromStatamic($item))
            ->values()
            ->toArray();

        $drawerItemsData = collect($drawerItems)
            ->filter(fn ($item) => $item['enabled'] ?? false)
            ->map(fn ($item) => NavItemData::fromStatamic($item))
            ->values()
            ->toArray();

        return new self(
            mainNavBar: $mainNavBarData,
            drawerItems: $drawerItemsData,
        );
    }

    /**
     * Convert to array for JSON response while maintaining typed structure
     */
    public function toResponseArray(): array
    {
        return [
            'main_nav_bar' => array_map(
                fn ($item) => $item instanceof NavItemData ? $item->toArray() : $item, 
                $this->mainNavBar
            ),
            'drawer_items' => array_map(
                fn ($item) => $item instanceof NavItemData ? $item->toArray() : $item, 
                $this->drawerItems
            ),
        ];
    }
}
