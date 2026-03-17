<?php

namespace App\Http\Controllers\API\V1\Content;

use Statamic\Entries\Entry;
use App\DataTypeObjects\File;
use App\Services\BunnyCDNService;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\DataTypeObjects\PlayableFile;
use App\Services\EntryBuilderService;
use App\DataTypeObjects\API\V1\Content\Prayers\PrayersShowResponse;
use App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview;

class PrayersEntryController extends Controller
{
    use ResolvesLivePreview;

    public function __construct(
        private readonly EntryBuilderService $entryBuilder,
        private readonly BunnyCDNService $bunnyCDN,
    ) {}

    public function index()
    {
        $prayers = EntryQueryBuilder::prayers()
            ->published()
            ->orderBy('order', 'asc')
            ->get();

        $prayersResponse = $prayers->map(function ($prayer) {
            return $this->buildPrayerResponse($prayer);
        });

        return PrayersShowResponse::collect($prayersResponse);
    }

    public function show($slug)
    {

        $prayer = $this->resolveLivePreview();

        if (! $prayer) {
            $prayer = EntryQueryBuilder::prayers()
                ->published()
                ->bySlug($slug)
                ->first();
        }

        if (! $prayer) {
            return response()->json(['error' => 'Prayer not found'], 404);
        }

        $response = $this->buildPrayerResponse($prayer);

        return PrayersShowResponse::from($response);
    }

    public function buildPrayerResponse(Entry $prayer)
    {
        $response = $this->entryBuilder->buildResponseFromEntry($prayer);

        if ($prayer->pdf) {
            $response['pdf'] = File::from(array_merge($prayer->pdf->toArray(), ['url' => $this->bunnyCDN->getBunnyCDNUrl($prayer->pdf)]));
        }
        if ($prayer->mp3) {
            $response['mp3'] = PlayableFile::from(array_merge($prayer->mp3->toArray(), ['url' => $this->bunnyCDN->getBunnyCDNUrl($prayer->mp3)]));
        }

        return $response;
    }
}
