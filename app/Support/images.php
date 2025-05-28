<?php

if (! function_exists('bunnyImage')) {
    function bunnyImage(string $image, array $params = [], string $cdn = 'api_url'): string
    {
        $baseUrl = config('services.bunny.' . ($cdn === 'api_url' ? 'api_url' : 's3_cdn_url'));

        return $baseUrl . '/' . $image . '?' . http_build_query($params);
    }
}
