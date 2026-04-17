<?php

use App\Models\User;
use App\Scopes\UserTrashed;
use Illuminate\Support\Facades\Bus;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    Bus::fake();
    $this->seed(DatabaseSeeder::class);

    $this->active = User::factory()->create(['email' => 'active@wildatheart.org']);
    $this->trashed = User::factory()->create(['email' => 'trashed@wildatheart.org']);
    $this->trashed->delete();
});

function applyTrashedFilter(string $value): array
{
    $filter = new UserTrashed;
    $query = User::query()->withTrashed();

    $filter->apply($query, ['trashed' => $value]);

    return $query->pluck('email')->all();
}

it('hides trashed users by default', function () {
    $emails = applyTrashedFilter('without');

    expect($emails)->toContain('active@wildatheart.org')
        ->and($emails)->not->toContain('trashed@wildatheart.org');
});

it('includes trashed users when "with" is selected', function () {
    $emails = applyTrashedFilter('with');

    expect($emails)->toContain('active@wildatheart.org')
        ->and($emails)->toContain('trashed@wildatheart.org');
});

it('shows only trashed users when "only" is selected', function () {
    $emails = applyTrashedFilter('only');

    expect($emails)->not->toContain('active@wildatheart.org')
        ->and($emails)->toContain('trashed@wildatheart.org');
});

it('is only visible to the user resource in Runway', function () {
    $filter = (new UserTrashed)->context(['resource' => 'user']);
    expect($filter->visibleTo('runway'))->toBeTrue();

    $filter = (new UserTrashed)->context(['resource' => 'event']);
    expect($filter->visibleTo('runway'))->toBeFalse();

    $filter = (new UserTrashed)->context(['resource' => 'user']);
    expect($filter->visibleTo('entries'))->toBeFalse();
});
