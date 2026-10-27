<?php

use App\Models\User;
use App\Support\ReadOnlyMode;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    config()->set('app.read_only', true);
});

it('keeps serving api reads', function () {
    $this->getJson('/api/v1/stats')->assertOk();
});

it('rejects api writes with a 503', function () {
    $user = User::factory()->create(['provider_id' => 'auth0|linked']);
    mockJwtAuthentication($user);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host-agreement')
        ->assertServiceUnavailable()
        ->assertHeader('Retry-After', 900)
        ->assertJson(['message' => ReadOnlyMode::MESSAGE]);
});

it('sends inertia writes back with a notification', function () {
    $this->from('/support')
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('/support', ['subject' => 'Hello'])
        ->assertRedirect('/support')
        ->assertSessionHas('notifications.message', ReadOnlyMode::MESSAGE);
});

it('blocks the reads that write', function (string $path) {
    $this->get($path)->assertServiceUnavailable();
})->with(['/login', '/callback', '/cp', '/cp/collections']);

it('lets users that are already linked use the api', function () {
    $user = User::factory()->create(['provider_id' => 'auth0|linked']);
    mockJwtAuthentication($user);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertOk();
});

it('does not link or create users', function () {
    $user = User::factory()->create(['provider_id' => null, 'email' => 'unlinked@wildatheart.org']);
    mockJwtAuthentication($user, ['sub' => 'auth0|unlinked']);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertServiceUnavailable();

    expect($user->fresh()->provider_id)->toBeNull();
});

it('changes nothing when read only mode is off', function () {
    config()->set('app.read_only', false);

    expect($this->get('/cp')->status())->not->toBe(503);

    $this->from('/support')
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('/support', ['subject' => 'Hello'])
        ->assertSessionMissing('notifications.message', ReadOnlyMode::MESSAGE);
});
