<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\Request;
use App\Queries\MapEventTypesQuery;

class HomeController extends Controller
{
    public function __construct(public SeoService $seoService) {}

    public function __invoke(Request $request)
    {
        $this->seoService->setMetadata(
            title: 'Home',
            description: 'Wild at Heart is a platform for locally hosted fires, experiences, and retreats. Find your next adventure today!',
            image: url('img/opengraph.png'),
            url: url()->current()
        );

        return inertia('HomePage', MapEventTypesQuery::get());
    }
}
