<?php

namespace WildAtHeart\CommandCenter\Http\Controllers\CP;

use Inertia\Inertia;
use Inertia\Response;
use Statamic\Facades\User;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Statamic\Http\Controllers\CP\CpController;

class CommandCenterController extends CpController
{
    public function index(): Response
    {
        abort_unless(User::current()?->isSuper(), 403);

        return Inertia::render('command-center::Index', [
            'features' => $this->resolveFeatures(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        abort_unless(User::current()?->isSuper(), 403);

        $request->validate([
            'feature' => ['required', 'string'],
            'active' => ['required', 'boolean'],
        ]);

        $feature = $request->string('feature');

        if ($request->boolean('active')) {
            Feature::activateForEveryone($feature);
        } else {
            Feature::deactivateForEveryone($feature);
        }

        return response()->json(['success' => true]);
    }

    public function triggerAstroBuild(): JsonResponse
    {
        abort_unless(User::current()?->isSuper(), 403);

        try {
            if (app()->environment('local', 'testing')) {
                return response()->json(['message' => 'Skipped Astro build in development environment']);
            }

            $response = Http::post(config('cloudflare.astro_build_hook'));

            return response()->json($response->json());
        } catch (RequestException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resolveFeatures(): array
    {
        $classFeatures = collect(File::allFiles(app_path('Features')))
            ->map(function ($file) {
                $relativePath = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

                return "App\\Features\\{$relativePath}";
            })
            ->filter(fn (string $class) => class_exists($class));

        $featureNames = collect(Feature::defined())
            ->merge(Feature::stored())
            ->merge($classFeatures)
            ->unique()
            ->sort()
            ->values();

        return $featureNames->map(fn (string $name) => [
            'name' => $name,
            'active' => Feature::for(null)->value($name) === true,
        ])->all();
    }
}
