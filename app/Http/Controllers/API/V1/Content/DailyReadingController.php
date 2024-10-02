<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\TransistorPodcastEpisode;
use App\Http\Controllers\Controller;
use App\Services\TransistorService;
use Illuminate\Http\Request;
/**
 * @group Daily Reading Entity Retrieval
 *
 * APIs for getting daily readings
 */
class DailyReadingController extends Controller
{
    public function index()
    {
        $transistorService = new TransistorService();
        $data = $transistorService->getAll('wildatheart-daily');
        return TransistorPodcastEpisode::collect($data);
    }

    
    public function getRecent(Request $request)
    {
        $limit = $request->input('limit', 1);
        
        $transistorService = new TransistorService();
        $data = $transistorService->getRecent('wildatheart-daily', $limit);
        return TransistorPodcastEpisode::collect($data);
    }

    public function show(string $slug)
    {
        $transistorService = new TransistorService();
        $data = $transistorService->transistorFetch('https://api.transistor.fm/v1/episodes/' . $slug );
        return TransistorPodcastEpisode::from($data);
    }
}
