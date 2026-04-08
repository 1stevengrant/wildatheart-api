<?php

namespace App\Http\Controllers\Internal;

use Carbon\Carbon;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Ai\Agents\EventReportingAssistant;

class AiAssistantController extends Controller
{
    public function index(): Response
    {
        $conversations = DB::table('agent_conversations')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(fn ($conversation) => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updated_at' => Carbon::parse($conversation->updated_at)->diffForHumans(),
            ]);

        return inertia('Internal/AiAssistantPage', [
            'conversations' => $conversations,
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'exists:agent_conversations,id'],
        ]);

        $agent = new EventReportingAssistant;
        $user = $request->user();

        if ($request->conversation_id) {
            $agent->continue($request->conversation_id, as: $user);
        } else {
            $agent->forUser($user);
        }

        $response = $agent->prompt($request->message);

        return response()->json([
            'message' => (string) $response,
            'conversation_id' => $response->conversationId,
        ]);
    }

    public function conversation(string $conversationId): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $conversation) {
            abort(404);
        }

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($message) => [
                'role' => $message->role,
                'content' => $message->content,
            ]);

        return response()->json([
            'id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $messages,
        ]);
    }
}
