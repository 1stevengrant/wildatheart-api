<?php

namespace App\Services;

use App\Models\Event;
use App\Builders\EventBuilder;
use Statamic\Facades\GlobalSet;
use App\Data\FeaturedContentData;

class PublicEventSearchService
{
    public function __construct(
        private readonly GeographyService $geographyService
    ) {}

    public function query(
        ?string $sex,
        ?string $typeGroup,
        ?string $types,
        ?string $startDate,
        ?string $endDate
    ): EventBuilder {
        return Event::query()
            ->published()
            ->active()
            ->public()
            ->filterBySex($sex)
            ->filterByTypeGroup($typeGroup)
            ->filterByEventTypes($types)
            ->betweenDates($startDate, $endDate);
    }

    public function featuredContent(
        float $swLatitude,
        float $swLongitude,
        float $neLatitude,
        float $neLongitude
    ): ?FeaturedContentData {
        $globalSet = GlobalSet::findByHandle('local_platform');

        if (! $globalSet) {
            return null;
        }

        $siteData = $globalSet->inDefaultSite()->data();

        if (! $siteData->get('featured_content_enabled')) {
            return null;
        }

        $showInRegions = $siteData->get('show_in_regions', []);

        if (empty($showInRegions)) {
            return FeaturedContentData::fromStatamicData($siteData);
        }

        $visibleRegions = $this->geographyService->getVisibleRegions(
            $swLatitude,
            $swLongitude,
            $neLatitude,
            $neLongitude
        );

        if (! array_intersect($showInRegions, $visibleRegions)) {
            return null;
        }

        return FeaturedContentData::fromStatamicData($siteData);
    }
}
