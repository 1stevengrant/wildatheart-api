<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Auth\CallbackController;

beforeEach(function () {
    Bus::fake();
});

function invokeFindOrCreateUser(array $auth0User): User
{
    $controller = (new ReflectionClass(CallbackController::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod($controller, 'findOrCreateUser');
    $method->setAccessible(true);

    return $method->invoke($controller, $auth0User);
}

it('creates a new user when no user exists for the email', function () {
    $auth0User = [
        'email' => 'newbie@wildatheart.org',
        'given_name' => 'New',
        'family_name' => 'Bie',
        'sub' => 'auth0|new-123',
    ];

    $user = invokeFindOrCreateUser($auth0User);

    expect(User::where('email', 'newbie@wildatheart.org')->count())->toBe(1)
        ->and($user->email)->toBe('newbie@wildatheart.org')
        ->and($user->provider_id)->toBe('auth0|new-123');
});

it('returns the existing user without duplicating when the email is already active', function () {
    $existing = User::factory()->create([
        'email' => 'returning@wildatheart.org',
        'provider_id' => 'auth0|original',
    ]);

    $user = invokeFindOrCreateUser([
        'email' => 'returning@wildatheart.org',
        'given_name' => 'Returning',
        'family_name' => 'User',
        'sub' => 'auth0|whatever',
    ]);

    expect($user->id)->toBe($existing->id)
        ->and(User::where('email', 'returning@wildatheart.org')->count())->toBe(1)
        ->and($user->provider_id)->toBe('auth0|original');
});

it('restores a soft-deleted user instead of throwing a unique constraint error on re-signup', function () {
    $original = User::factory()->create([
        'email' => 'comeback@wildatheart.org',
        'provider_id' => 'auth0|old-sub',
    ]);

    $original->delete();

    expect(User::find($original->id))->toBeNull()
        ->and(User::withTrashed()->find($original->id)->trashed())->toBeTrue();

    $user = invokeFindOrCreateUser([
        'email' => 'comeback@wildatheart.org',
        'given_name' => 'Come',
        'family_name' => 'Back',
        'sub' => 'auth0|new-sub',
    ]);

    expect($user->id)->toBe($original->id)
        ->and($user->trashed())->toBeFalse()
        ->and($user->provider_id)->toBe('auth0|new-sub')
        ->and(User::withTrashed()->where('email', 'comeback@wildatheart.org')->count())->toBe(1);
});

it('leaves the restored user\'s soft-deleted events trashed', function () {
    $user = User::factory()->create([
        'email' => 'hostcomeback@wildatheart.org',
    ]);
    $event = Event::factory()->fires()->create(['user_id' => $user->id]);

    $user->delete();

    expect(Event::find($event->id))->toBeNull()
        ->and(Event::withTrashed()->find($event->id)->trashed())->toBeTrue();

    invokeFindOrCreateUser([
        'email' => 'hostcomeback@wildatheart.org',
        'given_name' => 'Host',
        'family_name' => 'Back',
        'sub' => 'auth0|returned',
    ]);

    expect(Event::find($event->id))->toBeNull()
        ->and(Event::withTrashed()->find($event->id)->trashed())->toBeTrue();
});
