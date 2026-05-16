<?php

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Facade;

return [

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'Image' => Image::class,
        'Avatar' => Laravolt\Avatar\Facade::class,
    ])->toArray(),

];
