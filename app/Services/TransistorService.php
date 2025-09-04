<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Integrations\Transistor\TransistorConnector;
use App\Http\Integrations\Transistor\Requests\GetEpisodesRequest;

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

        $connector = new TransistorConnector;
        $request = new GetEpisodesRequest($showId);
        $request->query()->add('status', 'published');
        $request->query()->add('pagination[per]', $count);
        $json = $connector->send($request)->json();

        if (isset($json['errors']) || ! isset($json['data'])) {
            return $json;
        }

        return $json['data'];
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

        $connector = new TransistorConnector;
        $request = new GetEpisodesRequest($showId);
        $request->query()->add('status', $status);
        $justUpdated = false;

        $allEpisodes = Cache::rememberForever($url, function () use ($url, $request, $connector) {
            Cache::set($url . '_last_updated', now());
            $justUpdated = true;
            $request->disableCaching();
            $paginator = $connector->paginate($request);

            return $paginator->collect()->toArray();
        });

        $lastUpdated = Cache::get($url . '_last_updated');

        $recentEpisodes = $connector->send($request)->json();
        $updatedRecently = collect($recentEpisodes['data'])->filter(function ($episode) use ($lastUpdated) {
            return $lastUpdated->lt(data_get($episode, 'attributes.updated_at'));
        });

        // Only update episodes that have changed since the last time we got all of them.
        if (count($updatedRecently) > 0 && ! $justUpdated) {
            $allEpisodesCollection = collect($allEpisodes);
            foreach ($updatedRecently as $episode) {
                $index = $allEpisodesCollection->search(fn ($item) => $item['id'] === $episode['id']);
                if ((bool) $index) {
                    $allEpisodesCollection->put($index, $episode);
                } else {
                    $allEpisodesCollection->push($episode);
                }

                $allEpisodes = $allEpisodesCollection->toArray();

                Cache::set($url . '_last_updated', now());
                Cache::set($url, $allEpisodes);
            }
        }

        return $allEpisodes;
    }
}
