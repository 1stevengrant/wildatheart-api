<?php

use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

it('updates a user profile', function () {
    Bus::fake();
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user.profile'), [
            'family_name' => 'Doe',
            'given_name' => 'John',
            'phone' => '1234567890',
            'opted_into_marketing' => 1,
        ]);

    $user->refresh();

    expect($user->family_name)->toBe('Doe')
        ->and($user->given_name)->toBe('John')
        ->and($user->phone)->toBe('1234567890');
});

test('only authorised users can make profile updates', function () {
    $this->put(route('user.profile'), [
        'family_name' => 'Doe',
        'given_name' => 'John',
        'phone' => '1234567890',
        'opted_into_marketing' => 1,
    ])->assertRedirect(route('login'));
});
