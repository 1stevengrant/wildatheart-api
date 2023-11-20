<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('updates a user profile', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), [
            'family_name' => 'Doe',
            'given_name' => 'John',
            'phone' => '1234567890',
            'opted_into_marketing' => 1,
        ]);

    expect($this->user->fresh()->family_name)->toBe('Doe')->and(
        $this->user->fresh()->given_name)->toBe('John')->and(
            $this->user->fresh()->phone)->toBe('1234567890');
});

test('only authorised users can make profile updates', function () {
    $this->put(route('user.profile'), [
        'family_name' => 'Doe',
        'given_name' => 'John',
        'phone' => '1234567890',
        'opted_into_marketing' => 1,
    ])->assertRedirect(route('login'));
});
