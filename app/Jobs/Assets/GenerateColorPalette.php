<?php

namespace App\Jobs\Assets;

use ColorThief\ColorThief;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateColorPalette implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $asset,
        public int $colorCount = 3,
        public string $outputFormat = 'hex') {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $palette = $this->getPalette();

            $this->asset->set('color_palette', $palette)->save();
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'blank or transparent')) {
                $this->asset->set('color_palette', null)->save();

                return;
            }

            throw $e;
        }
    }

    protected function getPalette(): array
    {
        return ColorThief::getPalette(
            bunnyImage($this->asset->path, cdn: 's3_cdn_url'),
            colorCount: $this->colorCount,
            outputFormat: $this->outputFormat
        );
    }
}
