<?php

namespace App\Http\Controllers\API\V2\Navs;

use Illuminate\Http\Request;

class MobileNavController extends BaseNavController
{
    public function __invoke(Request $request)
    {
        return $this->getNavigationData();
    }

    protected function getGlobalSetHandle(): string
    {
        return 'mobile_nav';
    }

    protected function processNavigationData(array $data): array
    {
        // Get both navigation sections
        $mainNavBar = $data['main_nav_bar'] ?? [];
        $drawerItems = $data['drawer_items'] ?? [];

        // Return both sections filtered by enabled status
        return [
            'main_nav_bar' => $this->filterEnabledItems($mainNavBar),
            'drawer_items' => $this->filterEnabledItems($drawerItems),
        ];
    }
}
