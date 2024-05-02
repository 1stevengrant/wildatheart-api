<?php

use App\Models\EmailVerify;

it('logs a user in based on the token', function () {
    $user = \App\Models\User::factory()->create();
    $emailVerify = EmailVerify::create([
        'user_id' => $user->id,
    ]);

    $this->get(route('onetime.login', $emailVerify->token))
        ->assertRedirect(route('home'))
        ->assertSessionHas('notifications.message', 'You are logged in');

    $this->assertDatabaseMissing('email_verifies', [
        'id' => $emailVerify->id,
    ]);
    $this->assertAuthenticatedAs($user);
});

it('returns a 404 if the token is expired', function () {
    $user = \App\Models\User::factory()->create();
    $emailVerify = EmailVerify::create([
        'user_id' => $user->id,
        'expires_at' => now()->subDay(),
    ]);

    $this->get(route('onetime.login', $emailVerify->token))
        ->assertNotFound();

    $this->assertDatabaseMissing('email_verifies', [
        'id' => $emailVerify->id,
    ]);

    $this->assertGuest();
});

it('returns a 404 if the token is not found', function () {
    $this->get(route('onetime.login', 'not-a-token'))
        ->assertNotFound();
    $this->assertGuest();
});
