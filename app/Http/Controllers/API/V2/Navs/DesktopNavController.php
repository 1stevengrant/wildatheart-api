<?php

namespace App\Http\Controllers\API\V2\Navs;

use Illuminate\Http\Request;

class DesktopNavController extends BaseNavController
{
    public function __invoke(Request $request)
    {
        return $this->getNavigationData();
    }

    protected function getGlobalSetHandle(): string
    {
        return 'desktop_nav';
    }

    protected function processNavigationData(array $data): array
    {
        $navItems = $data['desktop_nav'] ?? [];

        // Filter to only enabled items and process dropdown links
        return collect($navItems)
            ->filter(fn ($item) => $item['enabled'] ?? false)
            ->map(fn ($item) => $this->filterNestedDropdownLinks($item))
            ->values()
            ->all();
    }
}
