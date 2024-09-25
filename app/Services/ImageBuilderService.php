<?php

namespace App\Services;

use Statamic\Entries\Entry;
use Statamic\Fields\Values;

class ImageBuilderService
{
    public function build(?Values $images)
    {
        if (!$images) {
            return null;
        }

        return $images;

        return array_map(function ($image) {
            return $image->toArray();
        }, $images);
    }
}
