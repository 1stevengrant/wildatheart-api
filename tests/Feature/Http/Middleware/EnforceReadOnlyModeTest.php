<?php

use App\Models\User;
use App\Models\Event;
use Laravel\Pennant\Feature;
use App\Support\ReadOnlyMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Exceptions\ReadOnlyModeException;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    Queue::fake();
});

function enableReadOnlyMode(): void
{
    // LazilyRefreshDatabase migrates on the first query, which the guard would block.
    DB::table('users')->exists();

    config()->set('app.read_only', true);
}

it('keeps serving api reads', function () {
    enableReadOnlyMode();

    $this->getJson('/api/v1/stats')->assertOk();
});

it('serves event payloads without pennant writing to the database', function () {
    $event = Event::factory()->fires()->create();
    DB::table('features')->delete();
    Feature::flushCache();
    enableReadOnlyMode();

    $this->getJson("/api/v1/events/{$event->id}")->assertOk();

    expect(DB::table('features')->count())->toBe(0);
});

it('rejects api writes with a 503', function () {
    $user = User::factory()->create(['provider_id' => 'auth0|linked']);
    mockJwtAuthentication($user);
    enableReadOnlyMode();

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host-agreement')
        ->assertServiceUnavailable()
        ->assertHeader('Retry-After', ReadOnlyMode::RETRY_AFTER_SECONDS)
        ->assertJson(['message' => ReadOnlyMode::MESSAGE]);
});

it('sends inertia writes back with a notification', function () {
    enableReadOnlyMode();

    $this->from('/support')
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('/support', ['subject' => 'Hello'])
        ->assertRedirect('/support')
        ->assertSessionHas('notifications.message', ReadOnlyMode::MESSAGE);
});

it('blocks the reads that write or queue work', function (string $path) {
    enableReadOnlyMode();

    $this->get($path)->assertServiceUnavailable();
})->with(['/login', '/callback', '/cp', '/cp/collections', '/contacts/export', '/internal/event-search/export']);

it('lets users that are already linked use the api', function () {
    $user = User::factory()->create(['provider_id' => 'auth0|linked']);
    mockJwtAuthentication($user);
    enableReadOnlyMode();

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertOk();
});

it('does not link users on an api read', function () {
    $user = User::factory()->create(['provider_id' => null, 'email' => 'unlinked@wildatheart.org']);
    mockJwtAuthentication($user, ['sub' => 'auth0|unlinked']);
    enableReadOnlyMode();

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertServiceUnavailable();

    config()->set('app.read_only', false);

    expect($user->fresh()->provider_id)->toBeNull();
});

it('blocks database writes from anywhere and logs them', function () {
    $user = User::factory()->create(['given_name' => 'Original']);
    Log::spy();
    enableReadOnlyMode();

    expect(fn () => $user->update(['given_name' => 'Changed']))->toThrow(ReadOnlyModeException::class)
        ->and(fn () => DB::table('users')->delete())->toThrow(ReadOnlyModeException::class)
        ->and(User::query()->find($user->id)->given_name)->toBe('Original');

    Log::shouldHaveReceived('warning')->with('Read only mode blocked a database write.', Mockery::any());
});

it('changes nothing when read only mode is off', function () {
    $user = User::factory()->create();

    expect($this->get('/cp')->status())->not->toBe(503);

    $user->update(['given_name' => 'Changed']);

    expect($user->fresh()->given_name)->toBe('Changed');

    $this->from('/support')
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('/support', ['subject' => 'Hello'])
        ->assertSessionMissing('notifications.message', ReadOnlyMode::MESSAGE);
});
