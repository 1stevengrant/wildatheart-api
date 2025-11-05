<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class SubsplashMediaItemData extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $summary,
        public ?string $description,
        public ?string $subtitle,
        public ?string $speaker,
        public ?int $durationMilliseconds,
        public ?string $duration,
        public string $shortCode,
        public ?string $audioDownloadUrl,
        public ?string $videoDownloadUrl,
        public ?string $wideImageUrl,
        public ?string $squareImageUrl,
        public ?string $bannerImageUrl,
        #[WithCast(DateTimeInterfaceCast::class)]
        public Carbon $date,
        public string $dateOnly,
        #[WithCast(DateTimeInterfaceCast::class)]
        public Carbon $updatedAt,
        public ?string $seriesId,
        public array $tags,
        public array $images,
    ) {}

    public function iFrameUrl()
    {
        return "https://subsplash.com/+f83c/embed/mi/+{$this->shortCode}?audio&video&logoWatermark";
    }

    public static function fromSubsplashResponse(array $mediaItem): self
    {
        $durationMilliseconds = data_get($mediaItem, '_embedded.video.duration')
          ?? data_get($mediaItem, '_embedded.audio._embedded.audio-outputs.0.duration', 0);

        // Audio processing
        $audioDownloadUrl = data_get($mediaItem, '_embedded.audio._embedded.audio-outputs.0._links.download.href');
        $audioFileName = data_get($mediaItem, '_embedded.audio.title', 'audio.mp3');
        if ($audioDownloadUrl) {
            $audioDownloadUrl = str_replace('{filename}.mp3', $audioFileName, $audioDownloadUrl);
        }

        // Video processing
        $videoDownloadUrl = data_get($mediaItem, '_embedded.video._embedded.video-outputs.0._links.related.href');
        $videoFileName = data_get($mediaItem, '_embedded.video.title', 'video.mp4');
        if ($videoDownloadUrl) {
            $videoDownloadUrl = str_replace('{filename}.mp4', $videoFileName, $videoDownloadUrl);
        }

        // Image processing
        $images = data_get($mediaItem, '_embedded.images', []);
        $wideImageUrl = data_get(collect($images)->where('type', 'wide')->first(), '_links.dynamic.href');
        $squareImageUrl = data_get(collect($images)->where('type', 'square')->first(), '_links.dynamic.href');
        $bannerImageUrl = data_get(collect($images)->where('type', 'banner')->first(), '_links.dynamic.href');

        if ($wideImageUrl) {
            $wideImageUrl = str_replace(['{name}', '{ext}', '{width}', '{height}'], ['image', 'jpg', '800', '450'], $wideImageUrl);
        }
        if ($squareImageUrl) {
            $squareImageUrl = str_replace(['{name}', '{ext}', '{width}', '{height}'], ['image', 'jpg', '800', '800'], $squareImageUrl);
        }
        if ($bannerImageUrl) {
            $bannerImageUrl = str_replace(['{name}', '{ext}', '{width}', '{height}'], ['image', 'jpg', '1920', '692'], $bannerImageUrl);
        }

        // Date processing
        $date = Carbon::parse(data_get($mediaItem, 'date'));

        return new self(
            id: data_get($mediaItem, 'id'),
            title: data_get($mediaItem, 'title'),
            summary: data_get($mediaItem, 'summary'),
            description: data_get($mediaItem, 'description'),
            subtitle: data_get($mediaItem, 'subtitle'),
            speaker: data_get($mediaItem, 'speaker'),
            durationMilliseconds: $durationMilliseconds,
            duration: $durationMilliseconds ? gmdate('H:i:s', $durationMilliseconds / 1000) : null,
            shortCode: data_get($mediaItem, 'short_code'),
            audioDownloadUrl: $audioDownloadUrl,
            videoDownloadUrl: $videoDownloadUrl,
            wideImageUrl: $wideImageUrl,
            squareImageUrl: $squareImageUrl,
            bannerImageUrl: $bannerImageUrl,
            date: $date,
            dateOnly: $date->format('Y-m-d'),
            updatedAt: Carbon::parse(data_get($mediaItem, 'updated_at')),
            seriesId: data_get($mediaItem, '_embedded.media-series.id'),
            tags: data_get($mediaItem, 'tags', []),
            images: $images,
        );
    }
}
