<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('that it sets a user as having agreed to the host agreement by adding a timestamp', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('host.agreement.store'))
        ->assertRedirect(route('host.event'));

    expect($user->fresh()->hasAgreedToHostAgreement())
        ->toBeTrue()
        ->and($user->agreed_to_host_agreement_date)
        ->toBeInstanceOf(\Carbon\Carbon::class);
});

test('an unauthenticated user cannot post to the host agreement endpoint', function () {
    $this->post(route('host.agreement.store'))
        ->assertRedirect(route('login'));
});
