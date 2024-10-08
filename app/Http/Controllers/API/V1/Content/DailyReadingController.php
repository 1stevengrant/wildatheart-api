<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\API\V1\Content\DailyReadingResponse;
use App\DataTypeObjects\ShowData;
use App\DataTypeObjects\TransistorPodcastEpisode;
use App\Http\Controllers\Controller;
use App\Services\TransistorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function generatePreviousEpisodeSlug(string $title)
    {




        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $title);

        $slug = urlencode($slug);

        $slug = strtolower($slug);

        // Remove any remaining non-alphanumeric characters (except hyphens)
        // $slug = preg_replace('/[^a-z0-9-]/i', '', $slug);

        if (str_contains($slug, "to-")) {
            $slug = str_replace("to-", "", $slug);
        }

        // Ensure single hyphens between words
        $slug = preg_replace('/-+/', '-', $slug);
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
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
                if (isset($item['attributes']['title'])) {
                    $title = $item['attributes']['title'];
                    $dailyReading->previous_episode_slug = $this->generatePreviousEpisodeSlug($title);
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
                    $dailyReading->previous_episode_slug = $this->generatePreviousEpisodeSlug($item['attributes']['title']);
                    $dailyReading->current_episode_slug = Str::slug($item['attributes']['title'], '-');
                }
                $dailyReadings[] = $dailyReading;
            }
        }
        return DailyReadingResponse::collection($dailyReadings);
    }

    public function redirect($slug)
    {
        $allReadings = $this->index();

        $mappedReadings = [];
        foreach ($allReadings as $reading) {
            $mappedReadings[$reading->previous_episode_slug] = $reading->current_episode_slug;
        }


        $slug = strtolower(urlencode($slug));
        if (isset($mappedReadings[$slug])) {
            return [
                'previous_slug' => $slug,
                'current_slug' => $mappedReadings[$slug]
            ];
        }

        // If no matching slug is found, return a 404 response
        return response()->json(['error' => 'Daily reading not found'], 404);
    }
}
