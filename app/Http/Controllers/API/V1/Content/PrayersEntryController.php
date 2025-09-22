<?php

namespace App\Http\Controllers\API\V1\Content;

use Statamic\Entries\Entry;
use Statamic\Facades\Token;
use App\DataTypeObjects\File;
use App\Services\BunnyCDNService;
use App\Http\Controllers\Controller;
use Facades\Statamic\CP\LivePreview;
use App\DataTypeObjects\PlayableFile;
use App\Services\EntryBuilderService;
use App\DataTypeObjects\API\V1\Content\Prayers\PrayersShowResponse;

class PrayersEntryController extends Controller
{
    public function index()
    {
        $prayers = Entry::query()
            ->where('collection', 'prayers')
            ->where('published', true)
            ->orderBy('order', 'asc')
            ->get();

        $prayersResponse = $prayers->map(function ($prayer) {
            return $this->buildPrayerResponse($prayer);
        });

        return PrayersShowResponse::collection($prayersResponse);
    }

    public function show($slug)
    {

        // Check for token and live-preview query parameters
        $request = request();
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        // If token is present or live-preview is true, adjust the query to include unpublished and future items

        $prayer = null;

        if ($token && $livePreview) {
            // Skip published and date checks for live preview or token access

            $token = Token::find($token);
            $prayer = LivePreview::item($token);
        } else {

            $prayer = Entry::query()
                ->where('collection', 'prayers')
                ->where('slug', $slug)
                ->where('published', true)
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
