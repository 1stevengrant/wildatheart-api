<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\UserConnectedToHost;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Mail\Event\EventInterestCancelled;
use App\Mail\Event\EventInterestRegistered;
use Illuminate\Support\Facades\RateLimiter;
use App\Jobs\Admin\PostUserInterestToSlackJob;
use App\Services\Auth0\JwtAccessTokenVerifier;
use Auth0\SDK\Exception\InvalidTokenException;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->eventId = (string) Str::uuid();
    $this->sub = 'email|' . Str::random(24);

    $this->host = User::factory()->create(['email' => 'host@example.test']);
    $this->event = Event::factory()->fires()->approved()->for($this->host)->create([
        'id' => $this->eventId,
    ]);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn([
            'sub' => $this->sub,
            'email' => 'jwt.user@example.test',
            'email_verified' => true,
            'given_name' => 'Jwt',
            'family_name' => 'User',
        ]);
});

function bearer(): array
{
    return ['Authorization' => 'Bearer fake.jwt.token'];
}

it('returns 404 when the user has no interest in the event', function () {
    $this->withHeaders(bearer())
        ->getJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNotFound();
});

it('lazily provisions a user on first authenticated request', function () {
    expect(User::where('provider_id', $this->sub)->exists())->toBeFalse();

    $this->withHeaders(bearer())
        ->getJson("/api/v1/events/{$this->eventId}/interests/me");

    $user = User::where('provider_id', $this->sub)->first();

    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('jwt.user@example.test')
        ->and($user->given_name)->toBe('Jwt')
        ->and($user->family_name)->toBe('User');
});

it('creates an interest row on POST and returns the payload', function () {
    $response = $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests");

    $response->assertOk()
        ->assertJsonStructure(['event_id', 'user_id', 'created_at'])
        ->assertJsonPath('event_id', $this->eventId);

    $user = User::where('provider_id', $this->sub)->first();
    expect($user)->not->toBeNull()
        ->and($response->json('user_id'))->toBe($user->id);

    $rows = DB::table('event_user_interests')
        ->where('user_id', $user->id)
        ->where('event_id', $this->eventId)
        ->count();

    expect($rows)->toBe(1);
});

it('is idempotent on repeated POST and preserves created_at', function () {
    $first = $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    $createdAt = $first->json('created_at');

    $this->travel(5)->seconds();

    $second = $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    expect($second->json('created_at'))->toBe($createdAt);

    $user = User::where('provider_id', $this->sub)->first();
    $rowCount = DB::table('event_user_interests')
        ->where('user_id', $user->id)
        ->where('event_id', $this->eventId)
        ->count();

    expect($rowCount)->toBe(1);
});

it('returns the same payload on GET after POST', function () {
    $created = $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    $fetched = $this->withHeaders(bearer())
        ->getJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertOk();

    expect($fetched->json('created_at'))->toBe($created->json('created_at'))
        ->and($fetched->json('event_id'))->toBe($created->json('event_id'))
        ->and($fetched->json('user_id'))->toBe($created->json('user_id'));
});

it('removes the interest on DELETE and is idempotent', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    $this->withHeaders(bearer())
        ->getJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNotFound();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();
});

it('returns 401 with missing_bearer when no token is provided', function () {
    $response = $this->getJson("/api/v1/events/{$this->eventId}/interests/me");

    $response->assertUnauthorized()
        ->assertJsonPath('error', 'missing_bearer');

    expect($response->headers->get('WWW-Authenticate'))
        ->toContain('error="missing_bearer"');
});

it('returns 401 with invalid_token when verification fails', function () {
    Mockery::resetContainer();

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andThrow(new InvalidTokenException('aud mismatch'));

    $response = $this->withHeaders(bearer())
        ->getJson("/api/v1/events/{$this->eventId}/interests/me");

    $response->assertUnauthorized()
        ->assertJsonPath('error', 'invalid_token');
});

it('falls back to /userinfo when the access token has no email claim', function () {
    Mockery::resetContainer();

    $newSub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn(['sub' => $newSub]);

    Http::fake([
        config('auth0.api.userinfo_url') => Http::response([
            'sub' => $newSub,
            'email' => 'userinfo.user@example.test',
            'email_verified' => true,
            'given_name' => 'Userinfo',
            'family_name' => 'Caller',
        ], 200),
    ]);

    $response = $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests");

    $response->assertOk();

    $user = User::where('provider_id', $newSub)->first();

    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('userinfo.user@example.test')
        ->and($user->given_name)->toBe('Userinfo')
        ->and($user->family_name)->toBe('Caller');

    Http::assertSentCount(1);
});

it('returns user_provisioning_failed when neither claims nor /userinfo yield an email', function () {
    Mockery::resetContainer();

    $newSub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn(['sub' => $newSub]);

    Http::fake([
        config('auth0.api.userinfo_url') => Http::response(['sub' => $newSub], 200),
    ]);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertUnauthorized()
        ->assertJsonPath('error', 'user_provisioning_failed');
});

it('skips /userinfo when the JWT already carries email', function () {
    Http::fake();

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Http::assertNothingSent();
});

it('skips /userinfo on subsequent requests for the same sub', function () {
    Mockery::resetContainer();

    $newSub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn(['sub' => $newSub]);

    Http::fake([
        config('auth0.api.userinfo_url') => Http::response([
            'sub' => $newSub,
            'email' => 'cache.test@example.test',
            'email_verified' => true,
        ], 200),
    ]);

    $this->withHeaders(bearer())->postJson("/api/v1/events/{$this->eventId}/interests")->assertOk();
    Http::assertSentCount(1);

    $this->withHeaders(bearer())->getJson("/api/v1/events/{$this->eventId}/interests/me")->assertOk();

    Http::assertSentCount(1); // still just one — second call resolved by provider_id
});

it('refuses to resurrect or link a soft-deleted user matched only by email', function () {
    $deleted = User::factory()->create([
        'email' => 'jwt.user@example.test',
        'provider_id' => null,
        'provider' => null,
    ]);
    $deleted->delete();

    expect($deleted->fresh()->trashed())->toBeTrue();

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertUnauthorized()
        ->assertJsonPath('error', 'account_disabled');

    $still = User::withTrashed()->where('email', 'jwt.user@example.test')->first();

    expect($still->trashed())->toBeTrue()
        ->and($still->provider_id)->toBeNull();
});

it('resurrects a soft-deleted user matched by provider_id', function () {
    $deleted = User::factory()->create([
        'email' => 'someone.else@example.test',
        'provider_id' => $this->sub,
        'provider' => 'auth0',
    ]);
    $deleted->delete();

    $this->withHeaders(bearer())
        ->getJson("/api/v1/events/{$this->eventId}/interests/me");

    $resurrected = User::where('provider_id', $this->sub)->first();

    expect($resurrected)->not->toBeNull()
        ->and($resurrected->id)->toBe($deleted->id)
        ->and($resurrected->trashed())->toBeFalse();
});

it('links the existing user when sub is new but email matches', function () {
    $existing = User::factory()->create([
        'email' => 'jwt.user@example.test',
        'provider_id' => null,
        'provider' => null,
    ]);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    $existing->refresh();

    expect($existing->provider_id)->toBe($this->sub)
        ->and($existing->provider)->toBe('auth0')
        ->and(User::query()->where('email', 'jwt.user@example.test')->count())->toBe(1);
});

it('refuses to link an existing user when the JWT email is unverified', function () {
    Mockery::resetContainer();

    $sub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn([
            'sub' => $sub,
            'email' => 'victim@example.test',
            'email_verified' => false,
            'given_name' => 'Mallory',
            'family_name' => 'Attacker',
        ]);

    $existing = User::factory()->create([
        'email' => 'victim@example.test',
        'provider_id' => null,
        'provider' => null,
    ]);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertUnauthorized()
        ->assertJsonPath('error', 'email_not_verified');

    $existing->refresh();

    expect($existing->provider_id)->toBeNull()
        ->and($existing->provider)->toBeNull();
});

it('refuses to create a new user when the JWT email is unverified', function () {
    Mockery::resetContainer();

    $sub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn([
            'sub' => $sub,
            'email' => 'unverified@example.test',
            'email_verified' => false,
        ]);

    Http::fake();

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertUnauthorized()
        ->assertJsonPath('error', 'email_not_verified');

    expect(User::where('email', 'unverified@example.test')->exists())->toBeFalse();
});

it('refuses to create a new user when /userinfo returns an unverified email', function () {
    Mockery::resetContainer();

    $sub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn(['sub' => $sub]);

    Http::fake([
        config('auth0.api.userinfo_url') => Http::response([
            'sub' => $sub,
            'email' => 'unverified-userinfo@example.test',
            'email_verified' => false,
        ], 200),
    ]);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertUnauthorized()
        ->assertJsonPath('error', 'email_not_verified');

    expect(User::where('email', 'unverified-userinfo@example.test')->exists())->toBeFalse();
});

it('returns 404 on POST when the event does not exist', function () {
    $missingEventId = (string) Str::uuid();

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$missingEventId}/interests")
        ->assertNotFound();
});

it('escapes HTML in user-controlled fields when rendering the host notification', function () {
    Mockery::resetContainer();

    $sub = 'email|' . Str::random(24);

    User::factory()->create([
        'provider_id' => $sub,
        'provider' => 'auth0',
        'email' => 'xss.user@example.test',
        'given_name' => '<script>alert(1)</script>',
        'family_name' => 'User',
        'phone' => '<b>boo</b>',
        'city' => '<i>City</i>',
        'state' => '<u>State</u>',
    ]);

    EmailTemplate::query()->updateOrCreate(
        ['slug' => 'attendee-registered'],
        [
            'name' => 'Attendee Registered',
            'subject' => 'New interest from [attendee_name]',
            'body' => 'attendee: [attendee_name] / phone: [attendee_phone] / city: [attendee_city] / state: [attendee_state] / email: [attendee_email]',
        ],
    );

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn([
            'sub' => $sub,
            'email' => 'xss.user@example.test',
            'email_verified' => true,
        ]);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(EventInterestRegistered::class, function ($mail) {
        $mail->content();
        $body = $mail->emailContent;

        return str_contains($body, '&lt;script&gt;alert(1)&lt;/script&gt;')
            && ! str_contains($body, '<script>alert(1)</script>')
            && str_contains($body, '&lt;b&gt;boo&lt;/b&gt;')
            && str_contains($body, '&lt;i&gt;City&lt;/i&gt;');
    });
});

it('sends the host email to the primary host on first interest', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(EventInterestRegistered::class, function ($mail) {
        return $mail->hasTo($this->host->email);
    });
});

it('CCs all managers on the host email when managers are attached', function () {
    $manager1 = User::factory()->create(['email' => 'manager1@example.test']);
    $manager2 = User::factory()->create(['email' => 'manager2@example.test']);
    $this->event->managers()->attach([$manager1->id, $manager2->id]);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(EventInterestRegistered::class, function ($mail) use ($manager1, $manager2) {
        return $mail->hasTo($this->host->email)
            && $mail->hasCc($manager1->email)
            && $mail->hasCc($manager2->email);
    });
});

it('sends the confirmation email to the interested user', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(UserConnectedToHost::class, function ($mail) {
        return $mail->hasTo('jwt.user@example.test');
    });
});

it('dispatches the Slack ping with the user and event', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Bus::assertDispatched(PostUserInterestToSlackJob::class, function ($job) {
        return $job->user->email === 'jwt.user@example.test'
            && $job->event->id === $this->eventId;
    });
});

it('dispatches the WeGive donor chain for the user', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Bus::assertChained([FindOrCreateDonorJob::class, TagDonorJob::class]);
});

it('does not fire side effects on a repeat POST while interest already exists', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(EventInterestRegistered::class, 1);
    Mail::assertQueued(UserConnectedToHost::class, 1);
    Bus::assertDispatchedTimes(PostUserInterestToSlackJob::class, 1);

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(EventInterestRegistered::class, 1);
    Mail::assertQueued(UserConnectedToHost::class, 1);
    Bus::assertDispatchedTimes(PostUserInterestToSlackJob::class, 1);
});

it('notifies the host when an existing interest is cancelled', function () {
    $interestedUser = User::factory()->create([
        'provider_id' => $this->sub,
        'provider' => 'auth0',
        'email' => 'jwt.user@example.test',
        'given_name' => 'Jwt',
        'family_name' => 'User',
    ]);

    DB::table('event_user_interests')->insert([
        'user_id' => $interestedUser->id,
        'event_id' => $this->eventId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Mail::fake();
    Bus::fake();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    Mail::assertQueued(EventInterestCancelled::class, function (EventInterestCancelled $mail) use ($interestedUser) {
        $mail->content();

        return $mail->hasTo($this->host->email)
            && $mail->hasReplyTo($interestedUser->email)
            && str_contains($mail->render(), 'Jwt User has cancelled their interest')
            && str_contains($mail->render(), $this->event->name);
    });
});

it('CCs event managers on the cancellation email', function () {
    $interestedUser = User::factory()->create([
        'provider_id' => $this->sub,
        'provider' => 'auth0',
        'email' => 'jwt.user@example.test',
    ]);
    $manager1 = User::factory()->create(['email' => 'manager1@example.test']);
    $manager2 = User::factory()->create(['email' => 'manager2@example.test']);
    $this->event->managers()->attach([$manager1->id, $manager2->id]);

    DB::table('event_user_interests')->insert([
        'user_id' => $interestedUser->id,
        'event_id' => $this->eventId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Mail::fake();
    Bus::fake();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    Mail::assertQueued(EventInterestCancelled::class, function (EventInterestCancelled $mail) use ($manager1, $manager2) {
        return $mail->hasTo($this->host->email)
            && $mail->hasCc($manager1->email)
            && $mail->hasCc($manager2->email);
    });
});

it('does not run unrelated registration side effects when interest is cancelled', function () {
    $interestedUser = User::factory()->create([
        'provider_id' => $this->sub,
        'provider' => 'auth0',
        'email' => 'jwt.user@example.test',
    ]);

    DB::table('event_user_interests')->insert([
        'user_id' => $interestedUser->id,
        'event_id' => $this->eventId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Mail::fake();
    Bus::fake();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    Mail::assertNotQueued(EventInterestRegistered::class);
    Mail::assertNotQueued(UserConnectedToHost::class);
    Bus::assertNotDispatched(PostUserInterestToSlackJob::class);
    Bus::assertNotDispatched(FindOrCreateDonorJob::class);
    Bus::assertNotDispatched(TagDonorJob::class);
});

it('does not notify anyone when cancelling without an existing interest', function () {
    Mail::fake();
    Bus::fake();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    Mail::assertNotQueued(EventInterestCancelled::class);
});

it('rate-limits the interests endpoints per authenticated user', function () {
    RateLimiter::clear('interests');

    $statuses = [];
    for ($i = 0; $i < 12; $i++) {
        $statuses[] = $this->withHeaders(bearer())
            ->getJson("/api/v1/events/{$this->eventId}/interests/me")
            ->status();
    }

    expect($statuses)->toContain(429);
});

it('fires side effects again after a delete + re-create cycle', function () {
    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    $this->withHeaders(bearer())
        ->deleteJson("/api/v1/events/{$this->eventId}/interests/me")
        ->assertNoContent();

    $this->withHeaders(bearer())
        ->postJson("/api/v1/events/{$this->eventId}/interests")
        ->assertOk();

    Mail::assertQueued(EventInterestRegistered::class, 2);
    Mail::assertQueued(UserConnectedToHost::class, 2);
    Bus::assertDispatchedTimes(PostUserInterestToSlackJob::class, 2);
});
