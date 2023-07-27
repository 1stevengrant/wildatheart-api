<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('makeImagePublic')) {
    function makeImagePublic(string $image): void
    {
        Storage::setVisibility($image, 'public');
    }
}

if (! function_exists('getImageUrl')) {
    function getImageUrl(string $image): string
    {
        return Storage::url($image);
    }
}

if (! function_exists('bunnyImage')) {
    function bunnyImage(string $image, array $params = []): string
    {
        return config('services.bunny.api_url').'/'.$image.'?'.http_build_query($params);
    }
}
