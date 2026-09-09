<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Models\Attendee;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use Carbon\CarbonInterface;
use App\Mail\HostInvitationMail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

/**
 * @return array<string, mixed>
 */
function publishEventPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Denver, CO',
        'city' => 'Denver',
        'zip' => '80202',
        'country' => 'US',
        'latitude' => 39.7392,
        'longitude' => -104.9903,
        'region' => 'Colorado',
        'district' => 'Denver County',
        'locality' => 'Denver',
        'neighborhood' => 'Downtown',
        'street_address' => '123 Main St',
        'is_private' => false,
        'state' => 'CO',
        'start_date' => now()->addDays(10)->format('Y-m-d'),
        'number_of_items_in_material_order' => '0',
    ], $overrides);
}

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

    mockJwtAuthentication($host);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
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

    mockJwtAuthentication($host);

    $saveResponse = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'name' => 'Denver, CO',
            'co_host_emails' => ['cohost@wildatheart.org', 'second@wildatheart.org'],
        ]);

    $saveResponse->assertOk()
        ->assertJsonPath('success', true);

    $draftId = $saveResponse->json('event.id');

    expect(EventManagerInvitation::where('event_id', $draftId)->pluck('email')->all())
        ->toEqual(['cohost@wildatheart.org', 'second@wildatheart.org']);

    $indexResponse = $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/host/events');

    $draft = collect($indexResponse->json('drafts'))->firstWhere('id', $draftId);

    expect($draft)->not->toBeNull();

    $pendingManagers = collect($draft['managers'])->where('is_pending', true)->values();

    expect($pendingManagers->pluck('email')->all())
        ->toEqual(['cohost@wildatheart.org', 'second@wildatheart.org']);
});

it('prevents a user from updating another users draft', function () {
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $otherUser = User::factory()->create(['email' => 'other@wildatheart.org']);
    $eventType = EventType::factory()->create();

    $otherDraft = Event::withoutEvents(fn () => Event::factory()
        ->for($otherUser)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $otherDraft->id,
            'name' => 'Hijacked Draft',
        ])
        ->assertForbidden();

    expect($otherDraft->fresh()->name)->not->toBe('Hijacked Draft');
});

it('returns 401 when creating a draft without authentication', function () {
    $eventType = EventType::factory()->create();

    $this->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
        'name' => 'Unauthenticated Draft',
    ])
        ->assertUnauthorized();
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

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $draft->id,
            'co_host_emails' => ['new@wildatheart.org'],
        ])
        ->assertOk();

    $emails = EventManagerInvitation::where('event_id', $draft->id)->pluck('email')->all();

    expect($emails)->toEqual(['new@wildatheart.org']);
});

it('publishes a draft and moves it to the listed bucket', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create([
        'email' => 'host@wildatheart.org',
    ]);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => false,
        'group_type' => 'retreat',
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            publishEventPayload(),
        )
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('event.id', $draft->id);

    expect($draft->fresh()->approved_at)->not->toBeNull();

    $indexResponse = $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/host/events');

    $listedIds = collect($indexResponse->json('listed'))->pluck('id');
    $draftIds = collect($indexResponse->json('drafts'))->pluck('id');

    expect($listedIds)->toContain($draft->id);
    expect($draftIds)->not->toContain($draft->id);
});

it('prevents a user from publishing another users draft', function () {
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $otherUser = User::factory()->create(['email' => 'other@wildatheart.org']);
    $eventType = EventType::factory()->create();

    $otherDraft = Event::withoutEvents(fn () => Event::factory()
        ->for($otherUser)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$otherDraft->id}/publish",
            publishEventPayload(),
        )
        ->assertForbidden();

    expect($otherDraft->fresh()->approved_at)->toBeNull();
});

it('prevents publishing an already published event', function () {
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();

    $publishedEvent = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$publishedEvent->id}/publish",
            publishEventPayload(),
        )
        ->assertForbidden();
});

it('returns validation errors when publishing without required fields and does not send co-host emails', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    EventManagerInvitation::create([
        'event_id' => $draft->id,
        'email' => 'cohost@wildatheart.org',
        'invited_by_user_id' => $host->id,
    ]);

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            [
                'name' => 'Incomplete Draft',
                'co_host_emails' => ['cohost@wildatheart.org'],
            ],
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude', 'longitude', 'start_date']);

    expect($draft->fresh()->approved_at)->toBeNull();
    Mail::assertNothingQueued();
});

it('uses the expected attendees label when materials order is missing on publish', function () {
    $host = User::factory()->create([
        'email' => 'host@wildatheart.org',
        'provider_id' => 'auth0|host-materials-label',
        'provider' => 'auth0',
    ]);
    Address::factory()->create([
        'user_id' => $host->id,
        'is_primary' => true,
    ]);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => true,
        'number_of_items_in_material_order_label' => 'Expected number of attendees',
        'group_type' => 'retreat',
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $payload = publishEventPayload();
    unset($payload['number_of_items_in_material_order']);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            $payload,
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['number_of_items_in_material_order'])
        ->assertJsonPath(
            'errors.number_of_items_in_material_order.0',
            'The expected number of attendees field is required.',
        );
});

it('dispatches zapier when materials order applies on publish', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    Address::factory()->create([
        'user_id' => $host->id,
        'is_primary' => true,
    ]);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => true,
        'zapier_webhook_url' => 'https://hooks.zapier.com/test',
        'group_type' => 'retreat',
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            publishEventPayload(['number_of_items_in_material_order' => '1 x 10-pack']),
        )
        ->assertOk();

    Bus::assertDispatched(PostToZapier::class);
});

it('sends co-host invitation emails when publishing', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => false,
        'group_type' => 'retreat',
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            publishEventPayload([
                'co_host_emails' => ['cohost@wildatheart.org', 'second@wildatheart.org'],
            ]),
        )
        ->assertOk();

    Mail::assertQueued(HostInvitationMail::class, 2);
});

it('does not send co-host emails when publishing without co-hosts', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => false,
        'group_type' => 'retreat',
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            publishEventPayload(),
        )
        ->assertOk();

    Mail::assertNothingQueued();
});

it('allows an owner to soft delete their event and cascades attendees', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));
    $attendee = Attendee::factory()->for($event)->create();

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($event->fresh()->deleted_at)->not->toBeNull();
    expect($attendee->fresh()->deleted_at)->not->toBeNull();
});

it('returns 403 when a co-host tries to delete an event', function () {
    Bus::fake();
    Mail::fake();

    $owner = User::factory()->create(['email' => 'owner@wildatheart.org']);
    $coHost = User::factory()->create(['email' => 'cohost@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($owner)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));
    $event->managers()->attach($coHost->id);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}")
        ->assertForbidden();

    expect($event->fresh()->deleted_at)->toBeNull();
});

it('returns 403 when another user tries to delete an event', function () {
    Bus::fake();
    Mail::fake();

    $owner = User::factory()->create(['email' => 'owner@wildatheart.org']);
    $otherUser = User::factory()->create(['email' => 'other@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($owner)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));

    mockJwtAuthentication($otherUser);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}")
        ->assertForbidden();

    expect($event->fresh()->deleted_at)->toBeNull();
});

it('returns 401 when deleting an event without authentication', function () {
    $event = Event::withoutEvents(fn () => Event::factory()->create([
        'approved_at' => now(),
    ]));

    $this->deleteJson("/api/v1/host/events/{$event->id}")
        ->assertUnauthorized();
});

it('allows an owner to unpublish a listed event', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/unpublish")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('event.id', $event->id)
        ->assertJsonPath('event.approved_at', null);

    expect($event->fresh()->approved_at)->toBeNull();
});

it('allows a co-host to unpublish a listed event', function () {
    Bus::fake();
    Mail::fake();

    $owner = User::factory()->create(['email' => 'owner@wildatheart.org']);
    $coHost = User::factory()->create(['email' => 'cohost@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($owner)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));
    $event->managers()->attach($coHost->id);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/unpublish")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($event->fresh()->approved_at)->toBeNull();
});

it('allows an owner to approve an unpublished event', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('event.id', $event->id);

    expect($event->fresh()->approved_at)->not->toBeNull();
});

it('allows a co-host to approve an unpublished event', function () {
    Bus::fake();
    Mail::fake();

    $owner = User::factory()->create(['email' => 'owner@wildatheart.org']);
    $coHost = User::factory()->create(['email' => 'cohost@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($owner)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));
    $event->managers()->attach($coHost->id);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($event->fresh()->approved_at)->not->toBeNull();
});

it('returns 403 when another user tries to unpublish an event', function () {
    Bus::fake();
    Mail::fake();

    $owner = User::factory()->create(['email' => 'owner@wildatheart.org']);
    $otherUser = User::factory()->create(['email' => 'other@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($owner)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));

    mockJwtAuthentication($otherUser);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/unpublish")
        ->assertForbidden();

    expect($event->fresh()->approved_at)->not->toBeNull();
});

it('returns 401 when unpublishing an event without authentication', function () {
    $event = Event::withoutEvents(fn () => Event::factory()->create([
        'approved_at' => now(),
    ]));

    $this->postJson("/api/v1/host/events/{$event->id}/unpublish")
        ->assertUnauthorized();
});

it('allows an owner to update a published event draft fields', function () {
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
            'name' => 'Original Name',
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $event->id,
            'name' => 'Updated Listed Event',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($event->fresh()->name)->toBe('Updated Listed Event');
    expect($event->fresh()->approved_at)->not->toBeNull();
});

it('persists expected attendees material order on draft save and returns it on host events index', function () {
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => true,
        'number_of_items_in_material_order_label' => 'Expected number of attendees',
    ]);

    mockJwtAuthentication($host);

    $saveResponse = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'name' => 'Woodland Park, CO',
            'latitude' => 38.9939,
            'longitude' => -105.0567,
            'start_date' => now()->addDays(14)->format('Y-m-d'),
            'number_of_items_in_material_order' => '2 x 10-pack',
            'expected_attendees' => '11-20',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $draftId = $saveResponse->json('event.id');

    expect($draftId)->not->toBeNull();
    expect(Event::find($draftId)?->number_of_items_in_material_order)->toBe('2 x 10-pack');

    $indexResponse = $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/host/events')
        ->assertOk();

    $draft = collect($indexResponse->json('drafts'))->firstWhere('id', $draftId);

    expect($draft)->not->toBeNull()
        ->and($draft['number_of_items_in_material_order'])->toBe('2 x 10-pack');
});
