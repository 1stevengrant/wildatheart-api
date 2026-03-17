<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

beforeEach(function () {
    Queue::fake();

    $user = User::factory()->create([
        'given_name' => 'John',
        'family_name' => 'Doe',
    ]);

    $eventType = EventType::first();

    $this->event = Event::factory()->create([
        'user_id' => $user->id,
        'event_type_id' => $eventType->id,
    ]);

    $this->validData = [
        'given_name' => 'Jane',
        'family_name' => 'Smith',
        'email' => 'jane.smith@example.com',
        'confirm_email' => 'jane.smith@example.com',
        'city' => 'Denver',
        'state' => 'Colorado',
        'country' => 'United States',
        'phone' => '1234567890',
        'opted_into_marketing' => true,
    ];
});

it('creates an attendee successfully with valid data', function () {
    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $this->validData
    );

    $response->assertSuccessful();

    $response->assertJsonStructure([
        'id',
        'event_id',
        'given_name',
        'family_name',
        'email',
        'phone',
        'city',
        'state',
        'country',
        'country_code',
        'marketing_updates',
        'created_at',
    ]);

    expect($response->json('given_name'))->toBe('Jane')
        ->and($response->json('family_name'))->toBe('Smith')
        ->and($response->json('email'))->toBe('jane.smith@example.com')
        ->and($response->json('marketing_updates'))->toBeTrue();

    $this->assertDatabaseHas('attendees', [
        'event_id' => $this->event->id,
        'given_name' => 'Jane',
        'family_name' => 'Smith',
        'email' => 'jane.smith@example.com',
    ]);
});

it('dispatches all required jobs when attendee opts into marketing', function () {
    $this->postJson(
        route('v1.events.connect.store', $this->event),
        $this->validData
    );

    Queue::assertPushed(LogActivityJob::class);
    Queue::assertPushed(SubscribeToCommunicationListJob::class);
    Queue::assertPushed(IncreaseEventCountsJob::class);
    Queue::assertPushed(PostAttendeeContactToSlackJob::class);
});

it('does not dispatch marketing job when attendee opts out of marketing', function () {
    $data = array_merge($this->validData, ['opted_into_marketing' => false]);

    $this->postJson(
        route('v1.events.connect.store', $this->event),
        $data
    );

    Queue::assertPushed(LogActivityJob::class);
    Queue::assertNotPushed(SubscribeToCommunicationListJob::class);
    Queue::assertPushed(IncreaseEventCountsJob::class);
    Queue::assertPushed(PostAttendeeContactToSlackJob::class);
});

it('validates required fields', function () {
    $response = $this->postJson(
        route('v1.events.connect.store', $this->event)
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'given_name',
        'family_name',
        'email',
        'confirm_email',
        'city',
        'state',
        'country',
        'phone',
        'opted_into_marketing',
    ]);
});

it('validates email format', function () {
    $data = array_merge($this->validData, [
        'email' => 'invalid-email',
        'confirm_email' => 'invalid-email',
    ]);

    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $data
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('validates email confirmation matches', function () {
    $data = array_merge($this->validData, [
        'confirm_email' => 'different@example.com',
    ]);

    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $data
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['confirm_email']);
});

it('validates minimum length for names', function () {
    $data = array_merge($this->validData, [
        'given_name' => 'J',
        'family_name' => 'S',
    ]);

    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $data
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['given_name', 'family_name']);
});

it('validates phone number length', function () {
    $data = array_merge($this->validData, [
        'phone' => '12345',
    ]);

    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $data
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['phone']);
});

it('validates opted_into_marketing is boolean', function () {
    $data = array_merge($this->validData, [
        'opted_into_marketing' => 'not-a-boolean',
    ]);

    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $data
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['opted_into_marketing']);
});

it('returns 404 for non-existent event', function () {
    $response = $this->postJson(
        '/api/v1/events/non-existent-id/connect',
        $this->validData
    );

    $response->assertNotFound();
});

it('rate limits to 3 requests per minute per IP', function () {
    // First 3 requests should succeed
    for ($i = 0; $i < 3; $i++) {
        $response = $this->postJson(
            route('v1.events.connect.store', $this->event),
            $this->validData
        );
        $response->assertSuccessful();
    }

    // 4th request should be rate limited
    $response = $this->postJson(
        route('v1.events.connect.store', $this->event),
        $this->validData
    );

    $response->assertStatus(429);
});
