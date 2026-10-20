<?php

namespace WildAtHeart\CommandCenter\Data;

use App\Models\User;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;

class AskConversationData
{
    public const int RECENT_LIMIT = 30;

    /**
     * @return array<int, array{id: string, title: string, updated_at: ?string}>
     */
    public static function recentFor(User $user): array
    {
        return $user->conversations()
            ->latest('updated_at')
            ->limit(self::RECENT_LIMIT)
            ->get(['id', 'title', 'updated_at'])
            ->map(fn (Conversation $conversation) => self::summary($conversation))
            ->all();
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     updated_at: ?string,
     *     messages: array<int, array{id: string, role: string, content: ?string, created_at: ?string}>
     * }
     */
    public static function withMessages(Conversation $conversation): array
    {
        $messages = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->oldest()
            ->get(['id', 'role', 'content', 'created_at'])
            ->map(fn (ConversationMessage $message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at?->toIso8601String(),
            ])
            ->all();

        return [
            ...self::summary($conversation),
            'messages' => $messages,
        ];
    }

    /**
     * @return array{id: string, title: string, updated_at: ?string}
     */
    private static function summary(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];
    }
}
