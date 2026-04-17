<?php

use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Database\Seeders\DatabaseSeeder;
use App\Actions\Statamic\RestoreUser;

beforeEach(function () {
    Bus::fake();
    $this->seed(DatabaseSeeder::class);
});

it('is only visible for trashed users', function () {
    $action = new RestoreUser;

    $active = User::factory()->create();
    $trashed = User::factory()->create();
    $trashed->delete();

    expect($action->visibleTo($active))->toBeFalse()
        ->and($action->visibleTo(User::withTrashed()->find($trashed->id)))->toBeTrue();
});

it('ignores non-User models', function () {
    $action = new RestoreUser;

    expect($action->visibleTo(new stdClass))->toBeFalse();
});

it('restores a soft-deleted user when run', function () {
    $action = new RestoreUser;

    $user = User::factory()->create(['email' => 'restore-me@wildatheart.org']);
    $user->delete();

    $result = $action->run(collect([User::withTrashed()->find($user->id)]), []);

    expect(User::find($user->id))->not->toBeNull()
        ->and(User::find($user->id)->trashed())->toBeFalse()
        ->and($result)->toBe('User restored');
});

it('restores multiple users and reports count', function () {
    $action = new RestoreUser;

    $one = User::factory()->create();
    $two = User::factory()->create();
    $one->delete();
    $two->delete();

    $items = User::withTrashed()->whereIn('id', [$one->id, $two->id])->get();

    $result = $action->run($items, []);

    expect(User::find($one->id))->not->toBeNull()
        ->and(User::find($two->id))->not->toBeNull()
        ->and($result)->toBe('Users restored');
});
