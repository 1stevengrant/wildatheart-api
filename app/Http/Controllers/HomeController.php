<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $image = url('img/opengraph.png');
        $title = 'Home - Wild at Heart Fires';
        $url = url()->current();
        SEOMeta::setTitle('Home')
            ->setCanonical($url);

        OpenGraph::setTitle($title)
            ->setUrl($url)
            ->addProperty('type', 'articles')
            ->addImage($image)
            ->addProperty('locale', 'en-US');

        TwitterCard::setTitle($title)
            ->addImage($image)
            ->setSite('@wildatheart');

        JsonLd::setTitle($title)
            ->addImage($image)
            ->setUrl($url);

        JsonLd::generate();

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
