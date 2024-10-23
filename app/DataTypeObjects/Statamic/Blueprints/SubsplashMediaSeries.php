<?php

namespace App\DataTypeObjects\Statamic\Blueprints;

use App\DataTypeObjects\Blueprint;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SubsplashMediaSeries extends Data
{
    public function __construct()
    {
        $this->blueprint = Blueprint::from(['title' => 'Subsplash Media Series', 'handle' => 'subsplash_media_series']);
    }

    public string $id;

    public string $title;

    public string $slug;

    public string $uri;

    public string $url;

    public Blueprint $blueprint;

    public string $subtitle;

    public array $subsplash_data;
}
