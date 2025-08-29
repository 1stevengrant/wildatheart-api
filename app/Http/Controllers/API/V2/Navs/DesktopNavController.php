<?php

namespace App\Http\Controllers\API\V2\Navs;

use Illuminate\Http\Request;
use App\Data\Navigation\DesktopNavResponseData;

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

        return DesktopNavResponseData::fromStatamicData($navItems)->toResponseArray();
    }
}
