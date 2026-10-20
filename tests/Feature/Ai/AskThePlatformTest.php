<?php

use App\Models\User;
use App\Ai\Agents\PlatformAnalyst;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Prompts\AgentPrompt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Http::preventStrayRequests();

    $this->superAdmin = User::factory()->create(['super' => true]);
});

it('shows the ask page to super users', function () {
    $this->actingAs($this->superAdmin)
        ->get(cp_route('command-center.ask.index', ['q' => 'How many fires?']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('command-center::Ask', shouldExist: false)
            ->where('initialQuestion', 'How many fires?')
            ->has('conversations', 0));
});

it('forbids control panel users who are not super users', function () {
    $user = User::factory()->create(['super' => false]);

    DB::table('role_user')->insert([
        'user_id' => $user->id,
        'role_id' => 'blog_editor',
    ]);

    $this->actingAs($user)
        ->get(cp_route('command-center.ask.index'))
        ->assertRedirect();

    $this->actingAs($user)
        ->getJson(cp_route('command-center.ask.conversations.show', ['conversationId' => 'x']))
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson(cp_route('command-center.ask.messages.store'), ['message' => 'Hello'])
        ->assertForbidden();
});

it('answers a question and starts a conversation', function () {
    PlatformAnalyst::fake(['There are **42** published fires.'])->preventStrayPrompts();

    $response = $this->actingAs($this->superAdmin)
        ->postJson(cp_route('command-center.ask.messages.store'), ['message' => 'How many published fires are there?'])
        ->assertOk()
        ->assertJsonPath('answer', 'There are **42** published fires.');

    PlatformAnalyst::assertPrompted('How many published fires are there?');

    $conversation = Conversation::findOrFail($response->json('conversation_id'));

    expect($conversation->participant_id)->toBe($this->superAdmin->id)
        ->and($conversation->messages()->pluck('role')->all())->toBe(['user', 'assistant']);
});

it('continues an existing conversation and shows its messages', function () {
    PlatformAnalyst::fake(fn (AgentPrompt|string $prompt) => str_contains($prompt instanceof AgentPrompt ? $prompt->prompt : $prompt, 'Second') ? 'Second answer' : 'First answer');

    $conversationId = $this->actingAs($this->superAdmin)
        ->postJson(cp_route('command-center.ask.messages.store'), ['message' => 'First question'])
        ->json('conversation_id');

    $this->actingAs($this->superAdmin)
        ->postJson(cp_route('command-center.ask.messages.store'), [
            'message' => 'Second question',
            'conversation_id' => $conversationId,
        ])
        ->assertOk()
        ->assertJsonPath('conversation_id', $conversationId);

    PlatformAnalyst::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Second question')
        && $prompt->agent->currentConversation() === $conversationId);

    $this->actingAs($this->superAdmin)
        ->getJson(cp_route('command-center.ask.conversations.show', ['conversationId' => $conversationId]))
        ->assertOk()
        ->assertJsonPath('messages.*.content', ['First question', 'First answer', 'Second question', 'Second answer']);
});

it('does not let a super user continue someone else\'s conversation', function () {
    PlatformAnalyst::fake(['Answer']);

    $otherAdmin = User::factory()->create(['super' => true]);

    $conversationId = PlatformAnalyst::make()
        ->forUser($otherAdmin)
        ->prompt('Private question')
        ->conversationId;

    $this->actingAs($this->superAdmin)
        ->postJson(cp_route('command-center.ask.messages.store'), [
            'message' => 'Follow up',
            'conversation_id' => $conversationId,
        ])
        ->assertNotFound();

    $this->actingAs($this->superAdmin)
        ->getJson(cp_route('command-center.ask.conversations.show', ['conversationId' => $conversationId]))
        ->assertNotFound();
});

it('validates the message', function () {
    PlatformAnalyst::fake()->preventStrayPrompts();

    $this->actingAs($this->superAdmin)
        ->postJson(cp_route('command-center.ask.messages.store'), ['message' => ''])
        ->assertJsonValidationErrors('message');

    PlatformAnalyst::assertNeverPrompted();
});

it('rate limits questions', function () {
    PlatformAnalyst::fake(fn () => 'Answer');

    foreach (range(1, 20) as $attempt) {
        $this->actingAs($this->superAdmin)
            ->postJson(cp_route('command-center.ask.messages.store'), ['message' => "Question {$attempt}"])
            ->assertOk();
    }

    $this->actingAs($this->superAdmin)
        ->postJson(cp_route('command-center.ask.messages.store'), ['message' => 'One too many'])
        ->assertTooManyRequests();
});
