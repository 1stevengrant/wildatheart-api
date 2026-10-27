<?php

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Facade;

return [

    'frontend_url' => env('FRONTEND_URL', env('APP_URL', 'http://localhost')),

    'read_only' => (bool) env('APP_READ_ONLY', false),

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'Image' => Image::class,
        'Avatar' => Laravolt\Avatar\Facade::class,
    ])->toArray(),

];
