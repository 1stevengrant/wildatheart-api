<?php

namespace WildAtHeart\CommandCenter\Http\Controllers\CP;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Container\Attributes\CurrentUser;
use WildAtHeart\CommandCenter\Data\AskConversationData;

class AskConversationsController extends CpController
{
    public function index(Request $request, #[CurrentUser] User $user): Response
    {
        return Inertia::render('command-center::Ask', [
            'conversations' => AskConversationData::recentFor($user),
            'initialQuestion' => $request->string('q')->limit(2000)->value() ?: null,
        ]);
    }

    public function show(string $conversationId, #[CurrentUser] User $user): JsonResponse
    {
        $conversation = $user->conversations()->findOrFail($conversationId);

        return response()->json(AskConversationData::withMessages($conversation));
    }
}
