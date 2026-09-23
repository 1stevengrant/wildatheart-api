<?php

namespace App\DataTypeObjects\API\V1\Content\Events;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The Runway `event_types` row linked from a landing page `related_event_type` field.
 */
#[TypeScript]
class EventTypeReference extends Data
{
    public int $id;

    public string $name;

    public string $api_url;
}
