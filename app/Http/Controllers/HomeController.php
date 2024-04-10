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
            'allEventTypes' => EventType::all(),
            'experienceTypes' => EventType::where('slug', 'like', '%experience%')->get(),
            'retreatTypes' => EventType::where('slug', 'like', '%retreat%')->orWhere('slug', 'like', '%school%')->get(),
            'fireTypes' => EventType::where('slug', 'like', '%fire%')->get(),
        ]);
    }
}
