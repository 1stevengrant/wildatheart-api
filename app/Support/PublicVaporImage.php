<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\VaporImage;

class PublicVaporImage extends VaporImage
{
    protected function storeFile($request, $requestAttribute): string
    {
        $filePath = parent::storeFile($request, $requestAttribute);

        $disk = Storage::disk($this->getStorageDisk());
        $disk->setVisibility(rtrim($filePath, '/'), 'public');

        return $filePath;
    }
}
