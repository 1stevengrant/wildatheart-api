<?php

namespace App\DataTypeObjects\Statamic\Fieldsets;

use Spatie\LaravelData\Data;

class SEO extends Data
{
    public ?string $seo_description = null;

    public ?array $seo_image = null;

    public ?string $seo_title = null;
}
