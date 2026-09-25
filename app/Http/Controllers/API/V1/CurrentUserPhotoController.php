<?php

namespace App\Http\Controllers\API\V1;

use App\Data\UserData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Data\V1\UpdateCurrentUserPhotoData;

class CurrentUserPhotoController extends Controller
{
    public function store(UpdateCurrentUserPhotoData $data): JsonResponse
    {
        $user = $this->user();

        $user->updateProfilePhoto($data->photo);

        return response()->json(UserData::fromModel($user->fresh()));
    }

    public function destroy(): JsonResponse
    {
        $user = $this->user();

        $user->deleteProfilePhoto();

        return response()->json(UserData::fromModel($user->fresh()));
    }
}
