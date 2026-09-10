<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravolt\Avatar\Facade as Avatar;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

test('full name returns Member if they have no first name or last name defined', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
    ]);

    expect($user->fullname)->toBe('Member');
});

it('returns an empty profile photo when avatar generation fails', function () {
    Avatar::shouldReceive('create')
        ->once()
        ->andThrow(new TypeError('Avatar generation failed'));

    $user = new User([
        'given_name' => 'Contact',
        'family_name' => 'User',
    ]);

    expect($user->profilePhoto())->toBe('');
});

it('returns given name and family name as the fullname if both are defined', function () {
    $user = User::factory()->create([
        'given_name' => 'John',
        'family_name' => 'Doe',
    ]);

    expect($user->fullname)->toBe('John Doe');
});

it('returns only the given name if family name is null', function () {
    $user = User::factory()->create([
        'given_name' => 'John',
        'family_name' => null,
    ]);

    expect($user->fullname)->toBe('John');
});

it('returns only the family name if given name is null', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => 'Doe',
    ]);

    expect($user->fullname)->toBe('Doe');
});

it('normalizes uppercase names', function () {
    $user = User::factory()->create([
        'given_name' => 'JIM',
        'family_name' => 'LYNUM',
    ]);

    expect($user->fullname)->toBe('Jim Lynum')
        ->and($user->getRawOriginal('given_name'))->toBe('Jim')
        ->and($user->getRawOriginal('family_name'))->toBe('Lynum');
});

it('preserves intentional mixed name casing', function () {
    $user = User::factory()->create([
        'given_name' => 'AJ',
        'family_name' => 'McDonald',
    ]);

    expect($user->fullname)->toBe('AJ McDonald');
});

test('a family and given name can be nullable', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
    ]);

    expect($user->family_name)->toBeNull()
        ->and($user->given_name)->toBeNull();
});

it('uses Laravel 13 model attributes for mass assignment and serialization', function () {
    $user = new User([
        'email' => 'member@example.com',
        'password' => 'secret-password',
    ]);

    expect($user->email)->toBe('member@example.com')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

test('when a user is deleted, their events and related items are also deleted', function () {
    $user = User::factory()->create();
    $event = Event::factory()->fires()->create([
        'user_id' => $user->id,
    ]);

    $attendee = Attendee::factory()->create([
        'event_id' => $event->id,
    ]);

    $address = Address::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    $user->delete();

    expect(User::find($user->id))->toBeNull()
        ->and(Event::find($event->id))->toBeNull()
        ->and(Attendee::find($attendee->id))->toBeNull()
        ->and(Address::find($address->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull();
});
