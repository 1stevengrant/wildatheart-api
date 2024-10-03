<?php


namespace App\DataTypeObjects\API\V1\Content;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class DailyReadingResponse extends Data
{
    /** @var Collection<int, DailyReadingResponse> */
    public Collection $transistor_episodes;
    public string $previous_episode_slug;
}