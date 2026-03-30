<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Jobs\LogActivityJob;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Mail\Event\ContactHostVerificationMail;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

function createContactToken(string $email, string $eventId, ?string $expiresAt = null): string
{
    return Crypt::encryptString(json_encode([
        'email' => $email,
        'event_id' => $eventId,
        'expires_at' => $expiresAt ?? now()->addHour()->toISOString(),
    ]));
}

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Queue::fake();
    Mail::fake();

    $host = User::factory()->create([
        'given_name' => 'Host',
        'family_name' => 'User',
    ]);

    $eventType = EventType::first();

    $this->event = Event::factory()->create([
        'user_id' => $host->id,
        'event_type_id' => $eventType->id,
    ]);

    $this->validConnectData = [
        'given_name' => 'Jane',
        'family_name' => 'Smith',
        'email' => 'jane.smith@gmail.com',
        'phone' => '1234567890',
        'opted_into_marketing' => true,
        'agreed_to_terms' => true,
        'contact_token' => createContactToken('jane.smith@gmail.com', $this->event->id),
    ];
});

describe('contact host verification (store)', function () {
    it('returns a generic message regardless of email existence', function () {
        $response = $this->postJson(
            route('v1.events.contact-host-verifications.store', $this->event),
            ['email' => 'nonexistent@gmail.com']
        );

        $response->assertSuccessful();

        expect($response->json('message'))->toContain('check your inbox');
    });

    it('returns the same message for existing users', function () {
        User::factory()->create(['email' => 'existing@gmail.com']);

        $response = $this->postJson(
            route('v1.events.contact-host-verifications.store', $this->event),
            ['email' => 'existing@gmail.com']
        );

        $response->assertSuccessful();

        expect($response->json('message'))->toContain('check your inbox');
    });

    it('sends a verification email', function () {
        $this->postJson(
            route('v1.events.contact-host-verifications.store', $this->event),
            ['email' => 'test@gmail.com']
        );

        Mail::assertQueued(ContactHostVerificationMail::class, function ($mail) {
            return $mail->hasTo('test@gmail.com');
        });
    });

    it('validates email is required', function () {
        $response = $this->postJson(
            route('v1.events.contact-host-verifications.store', $this->event),
            []
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    });
});

describe('contact host (store)', function () {
    it('creates a user and attendee with a valid token', function () {
        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $this->validConnectData
        );

        $response->assertSuccessful();

        expect($response->json('success'))->toBeTrue();

        $this->assertDatabaseHas('users', [
            'email' => 'jane.smith@gmail.com',
            'given_name' => 'Jane',
            'family_name' => 'Smith',
        ]);

        $user = User::where('email', 'jane.smith@gmail.com')->first();

        expect($user->provider_id)->toBeNull()
            ->and($user->agreed_to_terms_at)->not->toBeNull();

        $this->assertDatabaseHas('attendees', [
            'event_id' => $this->event->id,
            'contact_user_id' => $user->id,
            'given_name' => 'Jane',
            'family_name' => 'Smith',
            'email' => 'jane.smith@gmail.com',
        ]);
    });

    it('rejects an invalid token', function () {
        $data = array_merge($this->validConnectData, ['contact_token' => 'invalid-token']);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();

        expect($response->json('message'))->toContain('Invalid or expired');
    });

    it('rejects an expired token', function () {
        $expiredToken = createContactToken(
            'jane.smith@gmail.com',
            $this->event->id,
            now()->subHour()->toISOString()
        );

        $data = array_merge($this->validConnectData, ['contact_token' => $expiredToken]);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();

        expect($response->json('message'))->toContain('Invalid or expired');
    });

    it('rejects a token for a different event', function () {
        $otherEvent = Event::factory()->create([
            'user_id' => User::factory()->create()->id,
            'event_type_id' => EventType::first()->id,
        ]);

        $wrongToken = createContactToken('jane.smith@gmail.com', $otherEvent->id);

        $data = array_merge($this->validConnectData, ['contact_token' => $wrongToken]);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();

        expect($response->json('message'))->toContain('Invalid or expired');
    });

    it('rejects when email does not match the token', function () {
        $data = array_merge($this->validConnectData, [
            'email' => 'different@gmail.com',
        ]);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();

        expect($response->json('message'))->toContain('does not match');
    });

    it('does not duplicate user if email already exists', function () {
        $existingUser = User::factory()->create([
            'email' => 'jane.smith@gmail.com',
            'given_name' => 'Jane',
            'family_name' => 'Smith',
            'provider_id' => 'auth0|existing',
        ]);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $this->validConnectData
        );

        $response->assertSuccessful();

        expect(User::where('email', 'jane.smith@gmail.com')->count())->toBe(1);

        $this->assertDatabaseHas('attendees', [
            'contact_user_id' => $existingUser->id,
        ]);
    });

    it('sets agreed_to_terms_at on the user', function () {
        $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $this->validConnectData
        );

        $user = User::where('email', 'jane.smith@gmail.com')->first();

        expect($user->agreed_to_terms_at)->not->toBeNull();
    });

    it('does not overwrite existing user data', function () {
        User::factory()->create([
            'email' => 'jane.smith@gmail.com',
            'given_name' => 'Original',
            'family_name' => 'Name',
            'phone' => '9999999999',
        ]);

        $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $this->validConnectData
        );

        $user = User::where('email', 'jane.smith@gmail.com')->first();

        expect($user->given_name)->toBe('Original')
            ->and($user->family_name)->toBe('Name')
            ->and($user->phone)->toBe('9999999999');
    });

    it('dispatches all required jobs when attendee opts into marketing', function () {
        $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $this->validConnectData
        );

        Queue::assertPushed(LogActivityJob::class);
        Queue::assertPushed(SubscribeToCommunicationListJob::class);
        Queue::assertPushed(IncreaseEventCountsJob::class);
        Queue::assertPushed(PostAttendeeContactToSlackJob::class);
    });

    it('does not dispatch marketing job when attendee opts out', function () {
        $data = array_merge($this->validConnectData, ['opted_into_marketing' => false]);

        $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        Queue::assertPushed(LogActivityJob::class);
        Queue::assertNotPushed(SubscribeToCommunicationListJob::class);
        Queue::assertPushed(IncreaseEventCountsJob::class);
        Queue::assertPushed(PostAttendeeContactToSlackJob::class);
    });

    it('validates required fields', function () {
        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            []
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'given_name',
            'family_name',
            'email',
            'phone',
            'opted_into_marketing',
            'agreed_to_terms',
            'contact_token',
        ]);
    });

    it('validates agreed_to_terms must be accepted', function () {
        $data = array_merge($this->validConnectData, ['agreed_to_terms' => false]);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['agreed_to_terms']);
    });

    it('validates minimum name length', function () {
        $data = array_merge($this->validConnectData, [
            'given_name' => 'J',
            'family_name' => 'S',
        ]);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['given_name', 'family_name']);
    });

    it('validates phone number length', function () {
        $data = array_merge($this->validConnectData, ['phone' => '12345']);

        $response = $this->postJson(
            route('v1.events.contact-host.store', $this->event),
            $data
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['phone']);
    });
});
