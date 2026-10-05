<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Models\Attendee;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use Carbon\CarbonInterface;
use App\Mail\V1\HostInvitationMail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Jobs\Event\SendNewEventSlackAlert;
use App\Mail\Event\NewEventCreatedMailable;

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

it('lets a co-host edit an event without changing its hosts', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $eventType = EventType::factory()->create();

    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($owner)
        ->approved()
        ->create([
            'event_type_id' => $eventType->id,
            'name' => 'Original name',
        ]));
    $event->managers()->attach($coHost->id);

    EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => 'pending@wildatheart.org',
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $event->id,
            'name' => 'Renamed by co-host',
            'co_host_emails' => ['added-by-cohost@wildatheart.org'],
        ])
        ->assertOk();

    expect($event->fresh()->name)->toBe('Renamed by co-host')
        ->and(EventManagerInvitation::where('event_id', $event->id)->pluck('email')->all())
        ->toEqual(['pending@wildatheart.org']);

    Mail::assertNotQueued(HostInvitationMail::class);
});

it('immediately sends a co-host invitation email when inviting to an already published event', function () {
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
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $publishedEvent->id,
            'co_host_emails' => ['cohost@wildatheart.org'],
        ])
        ->assertOk();

    Mail::assertQueued(HostInvitationMail::class, 1);
});

it('does not resend a co-host invitation email for an already-pending invitation on a published event', function () {
    Mail::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create();

    $publishedEvent = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => now(),
        ]));

    EventManagerInvitation::create([
        'event_id' => $publishedEvent->id,
        'email' => 'cohost@wildatheart.org',
        'invited_by_user_id' => $host->id,
    ]);

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $publishedEvent->id,
            'co_host_emails' => ['cohost@wildatheart.org'],
        ])
        ->assertOk();

    Mail::assertNotQueued(HostInvitationMail::class);
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
    expect($draft->fresh()->gift_requested)->toBeFalse();
});

it('publishes a fire without an expected attendees count and orders one kit', function () {
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
        'group_type' => EventType::TYPE_FIRE,
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $payload = publishEventPayload(['frequency' => 'weekly']);
    unset($payload['number_of_items_in_material_order']);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            $payload,
        )
        ->assertOk();

    Bus::assertDispatched(
        PostToZapier::class,
        fn (PostToZapier $job) => $job->dataTransferObject->quantity === '1',
    );
    expect($draft->fresh()->gift_requested)->toBeTrue();
});

it('creates a fire without an expected attendees count and orders one kit', function () {
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
        'group_type' => EventType::TYPE_FIRE,
    ]);

    mockJwtAuthentication($host);

    $payload = publishEventPayload(['frequency' => 'weekly']);
    unset($payload['number_of_items_in_material_order']);

    $eventId = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events", $payload)
        ->assertOk()
        ->json('event.id');

    Bus::assertDispatched(
        PostToZapier::class,
        fn (PostToZapier $job) => $job->dataTransferObject->quantity === '1',
    );
    expect(Event::find($eventId)->gift_requested)->toBeTrue();
});

it('publishes a fire without ordering a kit when the host has no mailing address', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $eventType = EventType::factory()->create([
        'allow_materials_order' => true,
        'zapier_webhook_url' => 'https://hooks.zapier.com/test',
        'group_type' => EventType::TYPE_FIRE,
    ]);

    $draft = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
        ]));

    mockJwtAuthentication($host);

    $payload = publishEventPayload(['frequency' => 'weekly']);
    unset($payload['number_of_items_in_material_order']);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$draft->id}/publish",
            $payload,
        )
        ->assertOk();

    Bus::assertNotDispatched(PostToZapier::class);
    expect($draft->fresh()->gift_requested)->toBeFalse();
});

it('publishes a fire draft with materials orders from the dashboard without an expected attendees count', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create();
    $eventType = EventType::factory()->create([
        'allow_materials_order' => true,
        'group_type' => EventType::TYPE_FIRE,
    ]);
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
            'latitude' => 38.9939,
            'longitude' => -105.0567,
            'start_date' => now()->addDays(14),
            'frequency' => 'weekly',
            'number_of_items_in_material_order' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertOk();

    expect($event->fresh()->approved_at)->not->toBeNull();
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
            // Publishing requires a complete event; the factory's fire type needs a frequency.
            'start_date' => now()->addDays(14),
            'frequency' => 'weekly',
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
            // Publishing requires a complete event; the factory's fire type needs a frequency.
            'start_date' => now()->addDays(14),
            'frequency' => 'weekly',
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

it('stores a course draft\'s weekly frequency', function () {
    Mail::fake();

    $host = User::factory()->create();
    $eventType = EventType::factory()->create(['group_type' => EventType::TYPE_EXPERIENCE]);

    mockJwtAuthentication($host);

    $draftId = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'name' => 'Woodland Park, CO',
            'start_date' => now()->addDays(14)->format('Y-m-d'),
            'frequency' => 'weekly',
        ])
        ->assertOk()
        ->json('event.id');

    expect(Event::find($draftId)?->frequency?->value)->toBe('weekly');
});

it('rejects monthly and quarterly frequencies for courses', function (string $frequency) {
    $host = User::factory()->create();
    $eventType = EventType::factory()->create(['group_type' => EventType::TYPE_EXPERIENCE]);

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'name' => 'Woodland Park, CO',
            'frequency' => $frequency,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('frequency');
})->with(['monthly', 'quarterly']);

it('refuses to publish a draft from the dashboard until its required fields are filled', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create();
    $eventType = EventType::factory()->create(['group_type' => EventType::TYPE_FIRE]);
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
            'latitude' => null,
            'longitude' => null,
            'start_date' => null,
            'frequency' => null,
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude', 'start_date', 'frequency']);

    expect($event->fresh()->approved_at)->toBeNull();
});

it('publishes a complete fire draft from the dashboard', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create();
    $eventType = EventType::factory()->create(['group_type' => EventType::TYPE_FIRE]);
    $event = Event::withoutEvents(fn () => Event::factory()
        ->for($host)
        ->create([
            'event_type_id' => $eventType->id,
            'approved_at' => null,
            'latitude' => 38.9939,
            'longitude' => -105.0567,
            'start_date' => now()->addDays(14),
            'frequency' => 'weekly',
        ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertOk();

    expect($event->fresh()->approved_at)->not->toBeNull();
});

it('emails invites added to an unpublished event when it is published through the wizard', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create();
    $eventType = EventType::factory()->create(['group_type' => EventType::TYPE_EXPERIENCE]);
    $event = Event::withoutEvents(fn () => Event::factory()->for($host)->create([
        'event_type_id' => $eventType->id,
        'approved_at' => null,
        'start_date' => now()->addDays(10),
        'frequency' => 'weekly',
    ]));

    mockJwtAuthentication($host);

    $body = publishEventPayload([
        'event_id' => $event->id,
        'frequency' => 'weekly',
        'co_host_emails' => ['cohost@example.test'],
    ]);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", $body)
        ->assertOk();
    Mail::assertNotQueued(HostInvitationMail::class);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/{$event->id}/publish", $body)
        ->assertOk();

    Mail::assertQueued(HostInvitationMail::class, fn ($mail) => $mail->hasTo('cohost@example.test'));
});

it('emails invites added to a draft when it is published from the dashboard', function () {
    Bus::fake();
    Mail::fake();

    $host = User::factory()->create();
    $eventType = EventType::factory()->create(['group_type' => EventType::TYPE_FIRE]);
    $event = Event::withoutEvents(fn () => Event::factory()->for($host)->create([
        'event_type_id' => $eventType->id,
        'approved_at' => null,
        'start_date' => now()->addDays(10),
        'frequency' => 'weekly',
    ]));

    mockJwtAuthentication($host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", [
            'event_id' => $event->id,
            'co_host_emails' => ['cohost@example.test'],
        ])
        ->assertOk();
    Mail::assertNotQueued(HostInvitationMail::class);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertOk();

    Mail::assertQueued(HostInvitationMail::class, fn ($mail) => $mail->hasTo('cohost@example.test'));
});

it('saves a new wizard event as a draft without announcing it', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create();
    $eventType = EventType::where('name', EventType::FIRES)->firstOrFail();

    mockJwtAuthentication($host);

    $eventId = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", ['name' => "Test's"])
        ->assertOk()
        ->json('event.id');

    expect(Event::findOrFail($eventId)->is_draft)->toBeTrue();
    Bus::assertNotDispatched(SendNewEventSlackAlert::class);
    Mail::assertNotQueued(NewEventCreatedMailable::class);
});

it('announces a wizard draft when it is published', function () {
    Mail::fake();
    Bus::fake();

    $host = User::factory()->create();
    $eventType = EventType::where('name', EventType::FIRES)->firstOrFail();

    mockJwtAuthentication($host);

    $eventId = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$eventType->slug}/events/draft", ['name' => "Test's"])
        ->json('event.id');

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$eventType->slug}/events/{$eventId}/publish",
            publishEventPayload(['event_id' => $eventId, 'frequency' => 'weekly']),
        )
        ->assertOk();

    expect(Event::findOrFail($eventId)->is_draft)->toBeFalse();
    Bus::assertDispatchedTimes(SendNewEventSlackAlert::class, 1);
    Mail::assertQueued(NewEventCreatedMailable::class, fn ($mail) => $mail->hasTo($host->email));
});
