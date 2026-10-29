<?php

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Facade;

return [

    'name' => env('APP_NAME', 'Wild at Heart Local'),

    'frontend_url' => env('FRONTEND_URL', 'https://wildatheart.org/'),

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'Image' => Image::class,
        'Avatar' => Laravolt\Avatar\Facade::class,
    ])->toArray(),

];
