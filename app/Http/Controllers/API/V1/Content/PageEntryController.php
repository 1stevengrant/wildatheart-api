<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\PageData;
use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\Services\ImageBuilderService;
use Facades\Statamic\CP\LivePreview;
use Statamic\Entries\Entry;
use Statamic\Facades\Token;

/**
 * @group Page Entity Retrieval
 *
 * APIs for getting pages
 */
class PageEntryController extends Controller
{
    /**
     * getPageBySlug
     *
     * Get a page using its slug
     *
     * @urlParam slug string required The slug of the page. Example: media
     */
    public function getPageBySlug(string $slug)
    {

        // Check for token and live-preview query parameters
        $request = request();
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        // If token is present or live-preview is true, adjust the query to include unpublished and future items

        $page = null;

        if ($token && $livePreview) {
            // Skip published and date checks for live preview or token access

            $token = Token::find($token);
            $page = LivePreview::item($token);
        } else {

            $page = Entry::query()
                ->where('collection', 'pages')
                ->where('slug', $slug)
                ->where('published', true)
                ->where(function ($query) {
                    $query->where('date', '<=', now())
                        ->orWhere('data->make_scheduled_page_public', true);
                })
                ->first();
        }

        if (! $page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        $entryBuilder = new EntryBuilderService;

        $response = $entryBuilder->buildResponseFromEntry($page);

        $blockBuilder = new BlockBuilderService;

        $response['blocks'] = $blockBuilder->build($page->blocks);

        $imageBuilder = new ImageBuilderService;

        $response['images'] = $imageBuilder->build($page->images);

        // adding the json response here to overcome 500 error
        return response()->json($response);
        return PageData::from($response);
    }
}
