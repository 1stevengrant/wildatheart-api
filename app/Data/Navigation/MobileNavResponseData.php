<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MobileNavResponseData extends Data
{
    /**
     * @param  NavItemData[]  $mainNavBar
     * @param  NavItemData[]  $drawerItems
     */
    public function __construct(
        #[DataCollectionOf(NavItemData::class)]
        #[MapOutputName('main_nav_bar')]
        public DataCollection $mainNavBar,

        #[DataCollectionOf(NavItemData::class)]
        #[MapOutputName('drawer_items')]
        public DataCollection $drawerItems,
    ) {}

    /**
     * Create from Statamic data arrays
     */
    public static function fromStatamicData(array $mainNavBar, array $drawerItems): self
    {
        $mainCollection = NavItemData::collection(
            collect($mainNavBar)
                ->filter(fn ($item) => $item['enabled'] ?? false)
                ->map(fn ($item) => NavItemData::fromStatamic($item))
                ->values()
                ->toArray()
        );

        $drawerCollection = NavItemData::collection(
            collect($drawerItems)
                ->filter(fn ($item) => $item['enabled'] ?? false)
                ->map(fn ($item) => NavItemData::fromStatamic($item))
                ->values()
                ->toArray()
        );

        return new self(
            mainNavBar: $mainCollection,
            drawerItems: $drawerCollection,
        );
    }
}
