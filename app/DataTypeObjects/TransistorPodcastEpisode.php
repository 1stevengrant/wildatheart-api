<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class TransistorPodcastEpisode extends Data
{
    public string $id;

    public string $type;

    public Attributes $attributes;

    public Relationships $relationships;
}

#[TypeScript]
class Attributes extends Data
{
    public string $title;

    public int $number;

    public int $season;

    public string $status;

    public string $published_at;

    public ?int $duration;

    public bool $explicit;

    public string $keywords;

    public string $alternate_url;

    public string $media_url;

    public ?string $image_url;

    public string $video_url;

    public string $author;

    public ?string $summary;

    public string $description;

    public string $slug;

    public string $created_at;

    public string $updated_at;

    public string $formatted_published_at;

    public string $duration_in_mmss;

    public string $share_url;

    public ?string $transcript_url;

    #[LiteralTypeScriptType('Array<Record<string, unknown>>')]
    public array $transcripts;

    public string $formatted_summary;

    public string $formatted_description;

    public string $embed_html;

    public string $embed_html_dark;

    public bool $audio_processing;

    public string $type;

    public ?string $email_notifications;
}

#[TypeScript]
class Relationships extends Data
{
    public ShowData $show;
}
