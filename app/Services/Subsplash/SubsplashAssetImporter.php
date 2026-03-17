<?php

namespace App\Services\Subsplash;

use Illuminate\Support\Facades\Log;
use Statamic\Facades\AssetContainer;

class SubsplashAssetImporter
{
    public function importImage(array $image): ?\Statamic\Assets\Asset
    {
        $filename = 'subsplash_' . $image['id'] . '.jpg';
        $path = 'subsplash-imports/' . $filename;

        $existingAsset = \Statamic\Assets\Asset::query()
            ->where('path', $path)
            ->first();
        $sourceUrl = $image['_links']['related']['href'];

        if (! $existingAsset) {
            try {
                $imageContent = file_get_contents($sourceUrl);

                if ($imageContent !== false) {
                    $container = AssetContainer::all()->where('handle', 's3')->first();

                    if ($container) {
                        $container->disk()->put($path, $imageContent);

                        $asset = $container->makeAsset($path);
                        $asset->data([
                            'subsplash_image_id' => $image['id'],
                            'subsplash_image_type' => $image['type'],
                            'imported_at' => now(),
                        ]);
                        $asset->save();

                        $existingAsset = $asset;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to import Subsplash image: ' . $e->getMessage(), [
                    'image_id' => $image['id'],
                    'source_url' => $sourceUrl,
                ]);
            }
        }

        return $existingAsset;
    }

    public function processImages(array $images): array
    {
        return collect($images)
            ->filter(fn ($image) => isset($image['_links']['related']['href']))
            ->map(function ($image) {
                $sourceUrl = data_get($image, '_links.related.href');
                $statamicAsset = null;

                if ($sourceUrl) {
                    $statamicAsset = $this->importImage($image);
                }

                $imageData = [
                    'id' => data_get($image, 'id'),
                    'type' => 'new_image',
                    'enabled' => true,
                    'image_id_subsplash' => data_get($image, 'id'),
                    'image_type_subsplash' => data_get($image, 'type'),
                    'image_source_url_subsplash' => data_get($image, '_links.related.href'),
                    'image_dynamic_url_subsplash' => data_get($image, '_links.dynamic.href'),
                ];

                if ($statamicAsset) {
                    $imageData['statamic_asset_path'] = $statamicAsset->path();
                }

                return $imageData;
            })
            ->toArray();
    }
}
