<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;

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
