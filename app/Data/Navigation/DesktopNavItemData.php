<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DesktopNavItemData extends Data
{
    /**
     * @param  DropdownLinkData[]  $dropdownLinks
     */
    public function __construct(
        public string $id,
        public string $title,
        public bool $enabled = true,
        public string $type = 'main_nav_item',
        public ?string $url = null,
        public ?string $icon = null,
        #[DataCollectionOf(DropdownLinkData::class)]
        public ?DataCollection $dropdownLinks = null,
        public ?string $bottomLinkTitle = null,
        public ?string $bottomLinkUrl = null,
        public ?string $featuredMediaItemByline = null,
        public ?string $featuredMediaItemTitle = null,
        public ?string $featuredMediaItemUrl = null,
        public ?string $featuredMediaItemImage = null,
    ) {}

    /**
     * Create from Statamic data array
     */
    public static function fromStatamic(array $data): self
    {
        $dropdownLinks = null;
        if (isset($data['dropdown_links']) && is_array($data['dropdown_links'])) {
            $dropdownLinks = DropdownLinkData::collection(
                array_map(
                    fn ($link) => DropdownLinkData::fromStatamic($link),
                    $data['dropdown_links']
                )
            );
        }

        return new self(
            id: $data['id'] ?? uniqid(),
            title: $data['title'],
            enabled: $data['enabled'] ?? true,
            type: $data['type'] ?? 'main_nav_item',
            url: $data['url'] ?? null,
            icon: $data['icon'] ?? null,
            dropdownLinks: $dropdownLinks,
            bottomLinkTitle: $data['bottom_link_title'] ?? null,
            bottomLinkUrl: $data['bottom_link_url'] ?? null,
            featuredMediaItemByline: $data['featured_media_item_byline'] ?? null,
            featuredMediaItemTitle: $data['featured_media_item_title'] ?? null,
            featuredMediaItemUrl: $data['featured_media_item_url'] ?? null,
            featuredMediaItemImage: $data['featured_media_item_image'] ?? null,
        );
    }

    /**
     * Filter dropdown links to only enabled ones
     */
    public function withEnabledDropdownLinks(): self
    {
        if ($this->dropdownLinks === null) {
            return $this;
        }

        $filtered = $this->dropdownLinks->filter(fn ($link) => $link->enabled);

        return $this->clone()->with(dropdownLinks: $filtered);
    }
}
