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

it('invalidates the cache when the beta-tester role is attached', function () {
    $user = User::factory()->create(['beta_enabled' => false]);

    // Prime cache.
    expect(Feature::for($user)->active(BetaFeature::class))->toBeFalse();

    $user->assignRole(UserRoleEnum::BetaTester->value);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();
});

it('invalidates the cache when the beta-tester role is detached', function () {
    $user = User::factory()->create(['beta_enabled' => false]);
    $user->assignRole(UserRoleEnum::BetaTester->value);

    // Prime cache as true.
    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();

    $user->removeRole(UserRoleEnum::BetaTester->value);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeFalse();
});

it('does not invalidate the cache for non-beta-tester role changes', function () {
    Role::firstOrCreate(['name' => UserRoleEnum::Host->value]);

    $user = User::factory()->create(['beta_enabled' => false]);

    // Prime the cache so a row exists (resolves to false).
    expect(Feature::for($user)->active(BetaFeature::class))->toBeFalse();

    // Force the cached value to true — a state the resolver alone would never
    // produce for this user (beta_enabled is false). If the listener over-fires
    // and calls forget(), the resolver will re-run and return false, causing the
    // final assertion to fail.
    Feature::for($user)->activate(BetaFeature::class);

    $user->assignRole(UserRoleEnum::Host->value);

    // Cache should NOT have been invalidated — the forced value persists.
    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();
});
