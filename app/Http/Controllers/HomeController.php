<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Services\SeoService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(public SeoService $seoService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $this->seoService->setMetadata('Home', 'Wild at Heart is a platform for locally hosted fires, experiences, and retreats. Find your next adventure today!', url('img/opengraph.png'), url()->current());

        return inertia('HomePage', [
            'allEventTypes' => cache()->remember('allEventTypes', now()->addDay(), function () {
                return EventType::query()->active()->get();
            }),
            'experienceTypes' => cache()->remember('experienceTypes', now()->addDay(), function () {
                return EventType::query()->slug('%experience%')->active()->get();
            }),
            'retreatTypes' => cache()->remember('retreatTypes', now()->addDay(), function () {
                return EventType::query()->slug('%retreat%')->active()->get();
            }),
            'fireTypes' => cache()->remember('fireTypes', now()->addDay(), function () {
                return EventType::query()->slug('%fire%')->active()->get();
            }),
        ]);
    }
}
