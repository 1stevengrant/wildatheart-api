<?php

namespace WildAtHeart\CommandCenter\Http\Controllers\CP;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Ai\Agents\PlatformAnalyst;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Container\Attributes\CurrentUser;
use WildAtHeart\CommandCenter\Http\Requests\StoreAskMessageRequest;

class AskMessagesController extends CpController
{
    public function store(StoreAskMessageRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        set_time_limit(PlatformAnalyst::TIME_LIMIT_SECONDS);

        $conversationId = $request->validated('conversation_id');

        if ($conversationId) {
            $user->conversations()->findOrFail($conversationId);
        }

        $response = PlatformAnalyst::make()
            ->continueOrStart($conversationId, as: $user)
            ->prompt($request->validated('message'));

        return response()->json([
            'conversation_id' => $response->conversationId,
            'answer' => $response->text,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens ?? null,
                'output_tokens' => $response->usage->outputTokens ?? null,
            ],
        ]);
    }
}
