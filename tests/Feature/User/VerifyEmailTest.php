<?php

use App\Models\Campfire;
use App\Models\EmailVerify;
use App\Models\User;
use function Pest\Livewire\livewire;

it('verifies user email', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $campfire = Campfire::factory()->create([
        'approved_at' => null,
    ]);

    $token = EmailVerify::create([
        'user_id' => $user->id,
        'campfire_id' => $campfire->id,
    ]);

    livewire('verify-email', ['token' => $token->token])
        ->assertRedirect(route('gift', ['campfire' => $campfire]));

    $user->refresh();

    expect($user->email_verified_at)->not()->toBeNull()
        ->and(EmailVerify::find($token->id))->toBeNull()
        ->and(auth()->user()->id)->toEqual($user->id);

});
