<?php

namespace App\Http\Controllers\API\V2\Navs;

use Illuminate\Http\Request;
use App\Data\Navigation\MobileNavResponseData;

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
        $mainNavBar = $data['main_nav_bar'] ?? [];
        $drawerItems = $data['drawer_items'] ?? [];

        return MobileNavResponseData::fromStatamicData($mainNavBar, $drawerItems)->toArray();
    }
}
