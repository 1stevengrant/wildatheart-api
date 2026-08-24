<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    use HasRunwayResource;

    protected function propertiesJson(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->properties?->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?? '{}',
        );
    }
}
