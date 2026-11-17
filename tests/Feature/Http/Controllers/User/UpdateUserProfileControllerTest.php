<?php

use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Actions\User\CompleteUserProfile;

it('updates a user profile', function () {
    Bus::fake();
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/profile')
        ->put(route('user.profile'), [
            'family_name' => 'Doe',
            'given_name' => 'John',
            'phone' => '1234567890',
            'opted_into_marketing' => 1,
        ])
        ->assertRedirect('/profile')
        ->assertSessionHas('notifications', [
            'message' => 'User Profile Updated!',
            'type' => 'success',
        ]);

    $user->refresh();

    expect($user->family_name)->toBe('Doe')
        ->and($user->given_name)->toBe('John')
        ->and($user->phone)->toBe('1234567890')
        ->and($user->profile_step)->toBe(User::PROFILE_COMPLETE)
        ->and($user->opted_into_marketing)->toBeTrue();
});

it('reads opt out values from the request as booleans', function () {
    Bus::fake();

    $user = User::factory()->create(['opted_into_marketing' => true]);

    $this->actingAs($user)
        ->put(route('user.profile'), [
            'family_name' => 'Doe',
            'given_name' => 'John',
            'phone' => '1234567890',
            'opted_into_marketing' => '0',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->opted_into_marketing)->toBeFalse();
});

it('rejects invalid profile updates without running the action', function (array $payload, string $field) {
    CompleteUserProfile::mock()->shouldNotReceive('handle');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user.profile'), array_merge([
            'family_name' => 'Doe',
            'given_name' => 'John',
            'phone' => '1234567890',
            'opted_into_marketing' => 1,
        ], $payload))
        ->assertSessionHasErrors($field);
})->with([
    'missing given name' => [['given_name' => ''], 'given_name'],
    'short family name' => [['family_name' => 'D'], 'family_name'],
    'short phone' => [['phone' => '123'], 'phone'],
    'missing consent' => [['opted_into_marketing' => null], 'opted_into_marketing'],
    'non boolean consent' => [['opted_into_marketing' => 'maybe'], 'opted_into_marketing'],
    'non image upload' => [['image' => 'not-a-file'], 'image'],
]);

test('only authorised users can make profile updates', function () {
    $this->put(route('user.profile'), [
        'family_name' => 'Doe',
        'given_name' => 'John',
        'phone' => '1234567890',
        'opted_into_marketing' => 1,
    ])->assertRedirect(route('login'));
});
