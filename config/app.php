<?php

use Illuminate\Support\Facades\Facade;

return [

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'Image' => Intervention\Image\Facades\Image::class,
        'Avatar' => Laravolt\Avatar\Facade::class,
    ])->toArray(),

];
