<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\DataTypeObjects\TransistorPodcastEpisode;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DailyReadingResponse extends Data
{
    public TransistorPodcastEpisode $transistor_episode;

    public ?string $current_episode_slug;
}
