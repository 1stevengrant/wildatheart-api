<?php

use App\Models\User;
use App\Enums\UserRoleEnum;
use Laravel\Pennant\Feature;
use App\Features\BetaFeature;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRoleEnum::BetaTester->value]);
});

it('returns false for a user with no role and beta_enabled false', function () {
    $user = User::factory()->create(['beta_enabled' => false]);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeFalse();
});

it('returns true for a user with the beta-tester role', function () {
    $user = User::factory()->create(['beta_enabled' => false]);
    $user->assignRole(UserRoleEnum::BetaTester->value);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();
});

it('returns true when beta_enabled is true', function () {
    $user = User::factory()->create(['beta_enabled' => true]);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();
});

it('returns true when both role and beta_enabled are set', function () {
    $user = User::factory()->create(['beta_enabled' => true]);
    $user->assignRole(UserRoleEnum::BetaTester->value);

    expect(Feature::for($user)->active(BetaFeature::class))->toBeTrue();
});

it('returns false for an unauthenticated scope', function () {
    expect(Feature::for(null)->active(BetaFeature::class))->toBeFalse();
});
