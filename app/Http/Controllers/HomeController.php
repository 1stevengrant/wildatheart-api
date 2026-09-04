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
            'allEventTypes' => cache()->remember('allEventTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->active()->orderBy('order')->get()->toArray();
            }),
            'experienceTypes' => cache()->remember('experienceTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->slug('%experience%')->active()->orderBy('order')->get()->toArray();
            }),
            'retreatTypes' => cache()->remember('retreatTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->slug('%retreat%')->active()->orderBy('order')->get()->toArray();
            }),
            'fireTypes' => cache()->remember('fireTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->slug('%fire%')->active()->orderBy('order')->get()->toArray();
            }),
        ]);
    }
}
