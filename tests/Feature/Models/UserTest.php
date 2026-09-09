<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
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

it('returns a generated avatar string when the user has no uploaded photo', function () {
    $user = User::factory()->create([
        'image' => null,
        'given_name' => 'Nic',
        'family_name' => 'Bovee',
    ]);

    $photo = $user->profilePhoto();

    expect($photo)->toBeString()
        ->and($photo)->toStartWith('data:image')
        ->and(Cache::get('user_avatar_v2:' . md5($user->fullname)))->toBeString();
});

it('regenerates a string avatar when the app cache holds a non-string value', function () {
    $user = User::factory()->create([
        'image' => null,
        'given_name' => 'Nic',
        'family_name' => 'Bovee',
    ]);

    Cache::put('user_avatar_v2:' . md5($user->fullname), new stdClass);

    $photo = $user->profilePhoto();

    expect($photo)->toBeString()
        ->and($photo)->toStartWith('data:image')
        ->and(Cache::get('user_avatar_v2:' . md5($user->fullname)))->toBeString();
});

it('returns a string avatar when the laravolt cache holds a non-string value', function () {
    $user = User::factory()->create([
        'image' => null,
        'given_name' => 'Nic',
        'family_name' => 'Bovee',
    ]);

    $avatar = Avatar::create($user->fullname);
    $reflection = new ReflectionClass($avatar);
    $prefix = $reflection->getProperty('cacheKeyPrefix');
    $cacheKey = $reflection->getMethod('cacheKey');

    Cache::put($prefix->getValue($avatar) . $cacheKey->invoke($avatar), new stdClass);

    expect($user->profilePhoto())->toBeString()->toStartWith('data:image');
});

it('falls back to a placeholder when avatar generation fails', function () {
    $user = User::factory()->create([
        'image' => null,
        'given_name' => 'Nic',
        'family_name' => 'Bovee',
    ]);

    Avatar::shouldReceive('create')
        ->andThrow(new TypeError('Laravolt\\Avatar\\Avatar::toBase64(): Return value must be of type string, __PHP_Incomplete_Class returned'));

    expect($user->profilePhoto())->toBe(
        'https://ui-avatars.com/api/?name=' . urlencode($user->fullname) . '&background=random'
    );
});
