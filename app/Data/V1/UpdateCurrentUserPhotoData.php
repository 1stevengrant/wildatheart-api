<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;

class UpdateCurrentUserPhotoData extends Data
{
    public function __construct(
        public UploadedFile $photo,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }
}
