<?php

namespace App\Http\Controllers\API;

use App\Data\FeaturedContentData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeaturedContentController extends Controller
{
    public function __invoke(Request $request)
    {
        $featuredContent = \Statamic\Facades\GlobalSet::findByHandle('local_platform');
        $data = $featuredContent->inDefaultSite()->data();

        // Return null if featured content is not enabled
        if (! $data->get('featured_content_enabled')) {
            return response()->json(null);
        }

        return FeaturedContentData::fromStatamicData($data);
    }
}
