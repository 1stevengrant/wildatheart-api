<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ProfileMenuData extends Data
{
    public function __construct(
        public string $urlToProfile,
        /** @var array<int, ProfileMenuItemData> */
        #[DataCollectionOf(ProfileMenuItemData::class)]
        public array $primaryItems,
        /** @var array<int, ProfileMenuItemData> */
        #[DataCollectionOf(ProfileMenuItemData::class)]
        public array $secondaryItems,
    ) {}
}
