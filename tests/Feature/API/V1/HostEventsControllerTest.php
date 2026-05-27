<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

beforeEach(function () {
    config(['auth0.base_url' => 'https://example.auth0.com']);
});

it('returns 401 without a bearer token', function () {
    $this->getJson('/api/v1/host/events')
        ->assertUnauthorized();
});

it('returns listed and draft buckets for events controllable by the authenticated host', function () {
    $host = User::factory()->create([
        'email' => 'host@wildatheart.org',
    ]);
    $otherUser = User::factory()->create();
    $eventType = EventType::factory()->create();

    $createEvent = function (User $owner, ?CarbonInterface $approvedAt, int $startInDays) use ($eventType) {
        return Event::withoutEvents(fn () => Event::factory()
            ->for($owner)
            ->create([
                'event_type_id' => $eventType->id,
                'approved_at' => $approvedAt,
                'start_date' => now()->addDays($startInDays),
            ]));
    };

    $listedOwnedEvent = $createEvent($host, now(), 2);
    $draftOwnedEvent = $createEvent($host, null, 3);

    $listedManagedEvent = $createEvent($otherUser, now(), 4);
    $listedManagedEvent->managers()->attach($host->id);

    $listedOutsideControl = $createEvent($otherUser, now(), 5);
    $draftOutsideControl = $createEvent($otherUser, null, 6);

    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([
            'email' => $host->email,
            'sub' => 'auth0|host123',
        ]),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer fake-token')
        ->getJson('/api/v1/host/events');

    $response->assertOk()
        ->assertJsonStructure([
            'listed' => [['id', 'approved_at']],
            'drafts' => [['id', 'approved_at']],
        ]);

    $listedIds = collect($response->json('listed'))->pluck('id');
    $draftIds = collect($response->json('drafts'))->pluck('id');

    expect($listedIds->all())
        ->toContain($listedOwnedEvent->id, $listedManagedEvent->id)
        ->not->toContain($listedOutsideControl->id);

    expect($draftIds->all())
        ->toContain($draftOwnedEvent->id)
        ->not->toContain($draftOutsideControl->id);

    expect(collect($response->json('listed'))->pluck('approved_at')->every(fn ($approvedAt) => $approvedAt !== null))
        ->toBeTrue();

    expect(collect($response->json('drafts'))->pluck('approved_at')->every(fn ($approvedAt) => $approvedAt === null))
        ->toBeTrue();
});

it('persists co-host invitations when saving a draft and returns them on host events index', function () {
    Mail::fake();

    $host = User::factory()->create([
        'email' => 'host@wildatheart.org',
    ]);
    $eventType = EventType::factory()->create();

    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([
            'email' => $host->email,
            'sub' => 'auth0|host123',
        ]),
    ]);

    $saveResponse = $this->withHeader('Authorization', 'Bearer fake-token')
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'name' => 'Denver, CO',
            'co_host_emails' => ['cohost@wildatheart.org', 'second@wildatheart.org'],
        ]);

    $saveResponse->assertOk()
        ->assertJsonPath('success', true);

    $draftId = $saveResponse->json('event.id');

    expect(EventManagerInvitation::where('event_id', $draftId)->pluck('email')->all())
        ->toEqual(['cohost@wildatheart.org', 'second@wildatheart.org']);

    $indexResponse = $this->withHeader('Authorization', 'Bearer fake-token')
        ->getJson('/api/v1/host/events');

    $draft = collect($indexResponse->json('drafts'))->firstWhere('id', $draftId);

    expect($draft)->not->toBeNull();

    $pendingManagers = collect($draft['managers'])->where('is_pending', true)->values();

    expect($pendingManagers->pluck('email')->all())
        ->toEqual(['cohost@wildatheart.org', 'second@wildatheart.org']);
});

it('syncs co-host invitations when updating a draft', function () {
    Mail::fake();

    $host = User::factory()->create([
        'email' => 'host@wildatheart.org',
    ]);
    $eventType = EventType::factory()->create();

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    EventManagerInvitation::create([
        'event_id' => $draft->id,
        'email' => 'old@wildatheart.org',
        'invited_by_user_id' => $host->id,
    ]);

    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([
            'email' => $host->email,
            'sub' => 'auth0|host123',
        ]),
    ]);

    $this->withHeader('Authorization', 'Bearer fake-token')
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $draft->id,
            'co_host_emails' => ['new@wildatheart.org'],
        ])
        ->assertOk();

    $emails = EventManagerInvitation::where('event_id', $draft->id)->pluck('email')->all();

    expect($emails)->toEqual(['new@wildatheart.org']);
});
