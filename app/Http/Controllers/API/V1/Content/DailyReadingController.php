<?php

namespace App\Http\Controllers\API\V1\Content;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\TransistorService;
use App\Http\Controllers\Controller;
use App\Jobs\Transistor\RefreshDailyReadingsJob;
use App\DataTypeObjects\TransistorPodcastEpisode;
use App\DataTypeObjects\API\V1\Content\DailyReadingResponse;

/**
 * @group Daily Reading Entity Retrieval
 *
 * APIs for getting daily readings
 */
class DailyReadingController extends Controller
{
    public function __construct(private readonly TransistorService $transistorService) {}

    public function index()
    {
        $data = $this->transistorService->getAll('wildatheart-daily');
        $scheduledData = $this->transistorService->getAll('wildatheart-daily', 'scheduled');
        $draftData = $this->transistorService->getAll('wildatheart-daily', 'draft');
        $data = array_merge($data, $scheduledData, $draftData);
        $dailyReadings = [];

        /**
         * Drupal removed leading articles from slugs
         * for simplicity we'll do the same.
         */
        if ($data) {
            foreach ($data as &$item) {

                $dailyReading = DailyReadingResponse::from([
                    'transistor_episode' => TransistorPodcastEpisode::from($item),
                    'previous_episode_slug' => null,
                ]);

                if (isset($item['attributes']['title'])) {
                    $title = $item['attributes']['title'];
                    $dailyReading->current_episode_slug = Str::slug($title, '-');
                }
                $dailyReadings[] = $dailyReading;
            }
        }

        return DailyReadingResponse::collect($dailyReadings);
    }

    public function getRecent(Request $request)
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

    public function refreshDailyReadings()
    {
        RefreshDailyReadingsJob::dispatch(true)->delay(now()->addMinute());

        return response()->json(['message' => 'Daily readings refreshed']);
    }
}
