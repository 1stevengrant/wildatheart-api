<?php

namespace App\Console\Commands;

use Statamic\Facades\Asset;
use Illuminate\Console\Command;
use App\Jobs\Assets\GenerateColorPalette;

class GenerateColorPaletteForAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-color-palette-for-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and store color palettes for all image assets';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Asset::query()->chunk(100, function ($assets) {
            $assets->filter(function ($asset) {
                return $asset->container === 's3';
            })
                ->each(function ($asset) {
                    GenerateColorPalette::dispatch(asset: $asset);
                });
        });
    }
}
