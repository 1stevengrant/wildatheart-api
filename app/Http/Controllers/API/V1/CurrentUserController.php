<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Data\UserData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Data\V1\UpdateCurrentUserData;
use App\Data\V1\UserData as V1UserData;

class CurrentUserController extends Controller
{
    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return response()->json(UserData::fromModel($user));
    }

    public function update(UpdateCurrentUserData $data): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->fill($data->toArray())->save();

        return response()->json(V1UserData::fromUser($user->fresh()));
    }
}
