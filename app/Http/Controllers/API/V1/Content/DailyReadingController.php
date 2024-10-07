<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\API\V1\Content\DailyReadingResponse;
use App\DataTypeObjects\ShowData;
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

    private $transistorService;

    public function __construct(TransistorService $transistorService)
    {
        $this->transistorService = $transistorService;
    }

    public function generatePreviousEpisodeSlug(string $slug)
    {
        $slug = preg_replace('/^(to-|the-|a-|an-|of-)/i', '', $slug);
        $slug = preg_replace('/-to-|-the-|-a-|-an-|-of-/i', '-', $slug);
        return $slug;
    }


    public function index()
    {
        $data = $this->transistorService->getAll('wildatheart-daily');

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
                if (isset($item['attributes']['slug'])) {
                    $slug = $item['attributes']['slug'];
                    $dailyReading->previous_episode_slug = $this->generatePreviousEpisodeSlug($slug);
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
                if (isset($item['attributes']['slug'])) {
                    $dailyReading->previous_episode_slug = $this->generatePreviousEpisodeSlug($item['attributes']['slug']);
                }
                $dailyReadings[] = $dailyReading;
            }
        }
        return DailyReadingResponse::collection($dailyReadings);
    }
}
