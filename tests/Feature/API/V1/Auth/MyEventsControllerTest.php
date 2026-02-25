<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Database\Seeders\DatabaseSeeder;
use App\Http\Middleware\Auth0JwtMiddleware;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->withoutMiddleware(Auth0JwtMiddleware::class);

    $this->user = User::factory()->create([
        'given_name' => 'Test',
        'family_name' => 'Host',
        'provider_id' => 'auth0|test123',
    ]);

    $this->eventType = EventType::first();
});

it('returns active events for authenticated user', function () {
    $activeEvent = Event::factory()->approved()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $activeEvent->id);
});

it('returns inactive events when status is inactive', function () {
    // Use a retreat type (not fires) so start_date-based active/inactive logic applies
    $retreatType = EventType::where('name', EventType::WAH_RETREAT)->first();

    // Inactive event: published but past start date (non-fire type)
    $inactiveEvent = Event::factory()->approved()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $retreatType->id,
        'start_date' => now()->subMonth(),
        'auto_unpublished' => false,
    ]);

    // Active event should not appear
    Event::factory()->approved()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $retreatType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events', ['status' => 'inactive']));

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $inactiveEvent->id);
});

it('returns 401 when no bearer token is provided', function () {
    // Re-enable the middleware for this test
    $this->withMiddleware(Auth0JwtMiddleware::class);

    $response = $this->getJson('/api/v1/auth/me/events');

    $response->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('only returns events owned or managed by the user', function () {
    $otherUser = User::factory()->create();

    // User's own event
    Event::factory()->approved()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);

    // Other user's event (should not appear)
    Event::factory()->approved()->create([
        'user_id' => $otherUser->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events'));

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 1);
});

it('includes managed events for the user', function () {
    $otherUser = User::factory()->create();

    // Event managed by user (owned by someone else)
    $managedEvent = Event::factory()->approved()->create([
        'user_id' => $otherUser->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);
    $managedEvent->managers()->attach($this->user->id);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events'));

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $managedEvent->id);
});

it('paginates results with per_page parameter', function () {
    Event::factory()->approved()->count(5)->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events', ['per_page' => 2]));

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 5)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonCount(2, 'data');
});

it('excludes auto-unpublished events', function () {
    Event::factory()->approved()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events'));

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 0);
});

it('validates status parameter', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events', ['status' => 'invalid']));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

it('includes unpublished events as inactive', function () {
    // Unpublished event (no approved_at)
    Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type_id' => $this->eventType->id,
        'start_date' => now()->addMonth(),
        'auto_unpublished' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('v1.auth.me.events', ['status' => 'inactive']));

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 1);
});
