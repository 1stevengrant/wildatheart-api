<?php

namespace App\Http\Controllers\API\V1;

use App\Data\UserData;
use Spatie\LaravelData\Optional;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Data\V1\UpdateCurrentUserData;
use App\Data\V1\UserData as V1UserData;
use App\Actions\User\UpdateMarketingOptIn;

class CurrentUserController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(UserData::fromModel($this->user()));
    }

    public function update(UpdateCurrentUserData $data): JsonResponse
    {
        $user = $this->user();

        $user->fill($data->except('opted_into_marketing')->toArray())->save();

        if (! $data->opted_into_marketing instanceof Optional) {
            UpdateMarketingOptIn::run($user, $data->opted_into_marketing);
        }

        return response()->json(V1UserData::fromUser($user->fresh()));
    }
}
