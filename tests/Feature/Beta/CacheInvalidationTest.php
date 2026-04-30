<?php

use App\Models\User;
use App\Enums\UserRoleEnum;
use Laravel\Pennant\Feature;
use App\Features\BetaFeature;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRoleEnum::BetaTester->value]);
});

it('invalidates the cached BetaFeature value when beta_enabled changes', function () {
    $user = User::factory()->create(['beta_enabled' => false]);

    // Prime the cache (resolves to false, persisted in features table).
    expect(Feature::for($user)->active(BetaFeature::class))->toBeFalse();

    // Flip the column.
    $user->update(['beta_enabled' => true]);

    // Cache should be forgotten; resolver re-runs and returns true.
    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();
});

it('does not invalidate the cache when beta_enabled is unchanged', function () {
    $user = User::factory()->create(['beta_enabled' => false, 'name' => 'Original']);

    // Prime the cache so a features row exists.
    expect(Feature::for($user)->active(BetaFeature::class))->toBeFalse();

    expect(
        DB::table('features')
            ->where('name', BetaFeature::class)
            ->where('scope', $user::class . '|' . $user->id)
            ->exists()
    )->toBeTrue();

    // Update an unrelated attribute.
    $user->update(['name' => 'Updated']);

    // The cached row must still exist — observer should not have called forget.
    expect(
        DB::table('features')
            ->where('name', BetaFeature::class)
            ->where('scope', $user::class . '|' . $user->id)
            ->exists()
    )->toBeTrue();
});

it('invalidates the cache on soft-delete', function () {
    $user = User::factory()->create(['beta_enabled' => true]);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();

    $user->delete();

    // The features row for this scope should have been forgotten.
    expect(
        DB::table('features')
            ->where('name', BetaFeature::class)
            ->where('scope', $user::class . '|' . $user->id)
            ->exists()
    )->toBeFalse();
});
