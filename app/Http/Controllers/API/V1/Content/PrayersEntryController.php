<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\API\V1\Content\Prayers\PrayersShowResponse;
use App\DataTypeObjects\File;
use App\DataTypeObjects\PlayableFile;
use App\Http\Controllers\Controller;
use App\Services\BunnyCDNService;
use App\Services\EntryBuilderService;
use Statamic\Entries\Entry;

class PrayersEntryController extends Controller
{
    public function index()
    {
        $prayers = Entry::query()
            ->where('collection', 'prayers')
            ->where('published', true)
            ->get();


        $prayersResponse = $prayers->map(function ($prayer) {
            return $this->buildPrayerResponse($prayer);
        });

        return PrayersShowResponse::collect($prayersResponse);
    }

    public function show($slug)
    {

        $prayer = Entry::query()
            ->where('collection', 'prayers')
            ->where('slug', $slug)
            ->where('published', true)
            ->first();


        if (!$prayer) {
            return response()->json(['error' => 'Prayer not found'], 404);
        }

        $response = $this->buildPrayerResponse($prayer);

        return PrayersShowResponse::from($response);
    }

    public function buildPrayerResponse(Entry $prayer)
    {
        $entryBuilder = new EntryBuilderService;

        $response = $entryBuilder->buildResponseFromEntry($prayer);

        $bunnyCDNService = new BunnyCDNService;
        if ($prayer->pdf) {
            $response['pdf'] = File::from(array_merge($prayer->pdf->toArray(), ['url' => $bunnyCDNService->getBunnyCDNUrl($prayer->pdf)]));
        }
        if ($prayer->mp3) {
            $response['mp3'] = PlayableFile::from(array_merge($prayer->mp3->toArray(), ['url' => $bunnyCDNService->getBunnyCDNUrl($prayer->mp3)]));
        }

        return $response;
    }
}
