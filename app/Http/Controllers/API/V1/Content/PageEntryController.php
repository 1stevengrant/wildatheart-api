<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\PageData;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview;

/**
 * @group Page Entity Retrieval
 *
 * APIs for getting pages
 */
class PageEntryController extends Controller
{
    use ResolvesLivePreview;

    public function __construct(
        private readonly BlockBuilderService $blockBuilder,
        private readonly EntryBuilderService $entryBuilder,
    ) {}

    /**
     * Get a page using its slug
     *
     * @urlParam slug string required The slug of the page. Example: media
     */
    public function show(string $slug)
    {
        $page = $this->resolveLivePreview();

        if (! $page) {
            $page = EntryQueryBuilder::pages()
                ->published()
                ->bySlug($slug)
                ->visibleOrScheduled()
                ->first();
        }

        if (! $page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        $response = $this->entryBuilder->buildResponseFromEntry($page);

        $response['blocks'] = $this->blockBuilder->build($page->blocks);

        $response['images'] = $page->images;

        return PageData::from($response);
    }
}
