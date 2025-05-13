<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\DataTypeObjects\API\V1\EntryResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ExperienceResponse extends EntryResponse
{
    public ?array $blocks = null;

    public ?string $height = null;

    public ?array $images = null;

    public ?string $position = null;

    public ?array $sessions = null;

    public ?string $subtitle = null;

    public ?string $hero_logo = null;

    public ?array $hero_title = null;

    public int $updated_by = 0;

    public ?string $button_link = null;

    public ?string $button_text = null;

    public ?array $description = null;

    public ?string $leader_text = null;

    public ?string $hero_variant = null;

    public ?array $testimonials = null;

    public ?array $hero_subtitle = null;

    public ?array $experience_logo = null;

    public ?string $find_a_group_link = null;

    public ?string $host_a_group_link = null;

    public ?string $featured_video_url = null;

    public ?string $related_event_type = null;

    public ?string $subsplash_series_id = null;

    public ?string $background_video_url = null;

    public ?string $featured_video_fallback_image = null;

    public ?array $background_video_fallback_image = null;

    public ?array $sessionData = null;
}
