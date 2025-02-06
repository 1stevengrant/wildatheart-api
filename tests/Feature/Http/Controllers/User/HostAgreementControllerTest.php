<?php

use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

test('that it sets a user as having agreed to the host agreement by adding a timestamp', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('host.agreement.store'));

    expect($user->fresh()->hasAgreedToHostAgreement())
        ->toBeTrue()
        ->and($user->agreed_to_host_agreement_date)
        ->toBeInstanceOf(\Carbon\Carbon::class);
});

test('an unauthenticated user cannot post to the host agreement endpoint', function () {
    $this->post(route('host.agreement.store'))
        ->assertRedirect(route('login'));
});

test('that it redirects to the intended route after agreeing to the host agreement', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['host_agreement_redirect_to' => 'my.profile'])
        ->post(route('host.agreement.store'))
        ->assertRedirect(route('my.profile'));
});
