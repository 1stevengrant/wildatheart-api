<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Services\SeoService;
use Illuminate\Http\Request;

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

        return inertia('HomePage', [
            'allEventTypes' => cache()->remember('allEventTypes', now()->addDay(), function () {
                return EventType::query()->active()->orderBy('order')->get();
            }),
            'experienceTypes' => cache()->remember('experienceTypes', now()->addDay(), function () {
                return EventType::query()->slug('%experience%')->active()->orderBy('order')->get();
            }),
            'retreatTypes' => cache()->remember('retreatTypes', now()->addDay(), function () {
                return EventType::query()->slug('%retreat%')->active()->orderBy('order')->get();
            }),
            'fireTypes' => cache()->remember('fireTypes', now()->addDay(), function () {
                return EventType::query()->slug('%fire%')->active()->orderBy('order')->get();
            }),
        ]);
    }
}
