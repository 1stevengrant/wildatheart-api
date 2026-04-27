<?php

use App\Services\BunnyCDNService;

beforeEach(function () {
    config(['services.bunny.s3_cdn_url' => 'https://cdn.example.com']);
    $this->service = new BunnyCDNService;
});

it('returns null for null input', function () {
    expect($this->service->getBunnyCDNUrl(null))->toBeNull();
});

it('builds CDN URL from string path', function () {
    expect($this->service->getBunnyCDNUrl('files/image.jpg'))
        ->toBe('https://cdn.example.com/files/image.jpg');
});

it('builds CDN URL from string path without leading slash', function () {
    expect($this->service->getBunnyCDNUrl('uploads/photo.png'))
        ->toBe('https://cdn.example.com/uploads/photo.png');
});
