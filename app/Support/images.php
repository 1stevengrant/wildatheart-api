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
    function bunnyImage(string $image, array $params = [], string $cdn = 'api_url'): string
    {
        $baseUrl = config('services.bunny.'.($cdn === 'api_url' ? 'api_url' : 's3_cdn_url'));

        return $baseUrl.'/'.$image.'?'.http_build_query($params);
    }
}
