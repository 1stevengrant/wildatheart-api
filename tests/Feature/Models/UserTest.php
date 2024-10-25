<?php

use App\Models\Address;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});
test('full name returns the users email address if they have no first name or last name defined', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
    ]);
    expect($user->fullname)->toBe($user->email);
});

it('returns given name and family name as the fullname if both are defined', function () {
    $user = User::factory()->create([
        'given_name' => 'John',
        'family_name' => 'Doe',
    ]);

    expect($user->fullname)->toBe('John Doe');
});

test('a family and given name can be nullable', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
    ]);

    expect($user->family_name)->toBeNull()
        ->and($user->given_name)->toBeNull();
});

test('when a user is deleted, their events and related items are also deleted', function () {
    $user = User::factory()->create();
    $event = \App\Models\Event::factory()->fires()->create([
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
