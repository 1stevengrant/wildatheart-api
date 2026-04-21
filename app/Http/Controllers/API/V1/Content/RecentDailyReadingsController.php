<?php

namespace App\Http\Controllers\API\V1\Content;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\TransistorService;
use App\Http\Controllers\Controller;
use App\DataTypeObjects\TransistorPodcastEpisode;
use App\DataTypeObjects\API\V1\Content\DailyReadingResponse;

class RecentDailyReadingsController extends Controller
{
    public function __construct(private readonly TransistorService $transistorService) {}

    public function index(Request $request)
    {
        $limit = $request->input('limit', 1);

        $data = $this->transistorService->getRecent('wildatheart-daily', $limit);
        $dailyReadings = [];
        if ($data) {
            foreach ($data as &$item) {
                $dailyReading = DailyReadingResponse::from([
                    'transistor_episode' => TransistorPodcastEpisode::from($item),
                    'previous_episode_slug' => null,
                ]);
                if (isset($item['attributes']['title'])) {
                    $dailyReading->current_episode_slug = Str::slug($item['attributes']['title'], '-');
                    $dailyReading->uri = '/daily-reading/' . Str::slug($item['attributes']['title'], '-');
                }
                $dailyReadings[] = $dailyReading;
            }
        }

        return DailyReadingResponse::collect($dailyReadings);
    }
}
