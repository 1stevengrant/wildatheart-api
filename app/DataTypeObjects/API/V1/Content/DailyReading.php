<?php


namespace App\DataTypeObjects\API\V1\Content;

use App\DataTypeObjects\TransistorPodcastEpisode;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DailyReading extends Data
{
    public TransistorPodcastEpisode $transistor_episode;
    public ?string $previous_episode_slug;
}