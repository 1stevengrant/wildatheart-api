<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Integrations\Transistor\TransistorConnector;
use App\Http\Integrations\Transistor\Requests\GetEpisodesRequest;
use Illuminate\Support\Facades\Log;

class TransistorService
{
    public function updateEpisode($episodeId, $data)
    {
        $TRANSISTOR_API_KEY = config('services.transistor.api_key');

        $url = config('services.transistor.api_url') . 'episodes/' . $episodeId;

        $response = Http::withHeaders([
            'x-api-key' => $TRANSISTOR_API_KEY,
        ])->patch($url, $data);

        if ($response->successful()) {
            $json = $response->json();

            if (isset($json['errors'])) {
                return $json;
            }

            return $json;
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to update episode in Transistor API');
    }

    /**
     * Get the most recent episodes from the Transistor API
     *
     * @param  string  $showId  The ID of the show to fetch episodes from
     * @param  int  $count  The number of episodes to fetch
     * @return array The most recent episodes from the Transistor API
     */
    public function getRecent($showId, $count = 1)
    {
        return collect($this->getAll($showId, 'published'))->take($count)->toArray();
    }

    public function getEpisode($id, $showId)
    {
        $url = config('services.transistor.api_url') . 'episodes/' . $id;

        if (Cache::has($url)) {
            return Cache::get($url);
        }

        $allEpisodes = $this->getAll($showId);
        $episode = Cache::remember($url, 60, function () use ($allEpisodes, $id) {
            return collect($allEpisodes)->firstWhere('id', $id);
        });

        return $episode;
    }

    /**
     * Get all episodes from the Transistor API
     *
     * @param  string  $showId  The ID of the show to fetch episodes from
     * @param  string  $status  The status of the episodes to get.
     * @return array All episodes from the Transistor API
     */
    public function getAll($showId, $status = 'published')
    {

        $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=' . $status;

        if (Cache::has($url)) {
            return Cache::get($url);
        }
        $this->refreshEpisodeList($showId, $status);

        return Cache::get($url);
    }

    /**
     * Refresh the episode list from the Transistor API
     */
    public function refreshEpisodeList($showId, $status = 'published')
    {
        Log::info('Refreshing episode list for showId: ' . $showId . ' and status: ' . $status);
        $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=' . $status;

        $connector = new TransistorConnector;
        $request = new GetEpisodesRequest($showId);
        $request->query()->add('status', $status);
        $paginator = $connector->paginate($request);
        $episodes = $paginator->collect()->toArray();
        Cache::forever($url, $episodes);
    }
}
