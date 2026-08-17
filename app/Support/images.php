<?php

if (! function_exists('bunnyImage')) {
    function bunnyImage(string $image, array $params = [], string $cdn = 'api_url'): string
    {
        $baseUrl = config('services.bunny.' . ($cdn === 'api_url' ? 'api_url' : 's3_cdn_url'));

        // Encode each path segment (preserving slashes) so filenames containing
        // spaces, parentheses or other special characters produce a valid URL.
        // Link-preview crawlers (notably Apple's) reject unencoded og:image URLs
        // and fall back to an auto-generated image.
        $path = implode('/', array_map('rawurlencode', explode('/', $image)));

        return $baseUrl . '/' . $path . '?' . http_build_query($params);
    }
}

if (! function_exists('openGraphImage')) {
    /**
     * Normalise an image to the dimensions, aspect ratio and format that
     * link-preview crawlers (notably Apple's, used by iMessage) reliably render.
     * Oversized or oddly-proportioned source images are the common reason iOS
     * silently falls back to an auto-generated snapshot. Keep the 1200x630
     * dimensions in sync with any og:image:width / og:image:height tags.
     */
    function openGraphImage(string $image): string
    {
        return bunnyImage($image, [
            'width' => 1200,
            'height' => 630,
            'aspect_ratio' => '1200:630',
            'quality' => 80,
            'format' => 'jpg',
        ]);
    }
}
