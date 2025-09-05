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

        return DailyReadingResponse::collection($dailyReadings);
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

        return DailyReadingResponse::collection($dailyReadings);
    }

    public function redirect($slug)
    {
        // $allReadings = $this->index();

        $jsonPath = storage_path('app/public/dailyreadings.json');
        $jsonContents = file_get_contents($jsonPath);
        $dailyReadingsData = json_decode($jsonContents, true);

        if ($dailyReadingsData === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Error decoding JSON: ' . json_last_error_msg());
        }

        $matchingReading = null;
        $slug = mb_strtolower(urlencode($slug));
        foreach ($dailyReadingsData as $reading) {

            if (mb_strtolower(str_replace('/daily-reading/', '', $reading['alias'])) === $slug) {
                $matchingReading = $reading;

                break;
            }
        }

        if ($matchingReading) {
            return response()->json([
                'previous_slug' => str_replace('/daily-reading/', '', $reading['alias']),
                'current_slug' => Str::slug($matchingReading['title'], '-'),
            ]);
        }

        // If no matching slug is found, return a 404 response
        return response()->json(['error' => 'Daily reading not found'], 404);
    }

    public function refreshDailyReadings()
    {
        RefreshDailyReadingsJob::dispatch(true);

        return response()->json(['message' => 'Daily readings refreshed']);
    }
}
