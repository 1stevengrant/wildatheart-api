<?php

namespace Ghijk\WildAtHeart\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class TriggerAstroBuildController extends Controller
{
    public function __invoke()
    {
        try {
            if (app()->environment('local')) {
                return response()->json(['message' => 'Skipped Astro build in development environment']);
            }

            dump('Triggering Astro build');
            $response = Http::post(config('cloudflare.astro_build_hook'));

            return response()->json($response->json());
        } catch (RequestException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
