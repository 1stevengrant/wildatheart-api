<?php

use App\Models\User;
use App\Data\ProfileUpdateData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use App\Actions\User\CompleteUserProfile;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;
use App\Jobs\WeGive\UnsubscribeFromCommunicationListJob;

beforeEach(function () {
    Bus::fake();

    config(['services.wegive.marketing_list_id' => 'marketing-list']);

    $this->travelTo(now()->startOfDay()->addHours(10));

    $this->profile = fn (bool $optedIntoMarketing): ProfileUpdateData => new ProfileUpdateData(
        given_name: 'Jane',
        family_name: 'Smith',
        phone: '1234567890',
        opted_into_marketing: $optedIntoMarketing,
    );
});

it('stores the profile details and marks the profile complete', function () {
    $user = User::factory()->create(['profile_step' => 1, 'opted_into_marketing' => false]);

    $result = CompleteUserProfile::run($user, ($this->profile)(false));

    $user->refresh();

    expect($result->is($user))->toBeTrue()
        ->and($user->given_name)->toBe('Jane')
        ->and($user->family_name)->toBe('Smith')
        ->and($user->phone)->toBe('1234567890')
        ->and($user->profile_step)->toBe(User::PROFILE_COMPLETE)
        ->and($user->opted_into_marketing)->toBeFalse()
        ->and($user->date_opted_into_marketing)->toBeNull();

    Bus::assertNotDispatched(SubscribeToCommunicationListJob::class);
    Bus::assertNotDispatched(UnsubscribeFromCommunicationListJob::class);
});

it('subscribes on first completion with opt in even when consent was already given', function () {
    $user = User::factory()->create(['profile_step' => 1, 'opted_into_marketing' => true]);

    CompleteUserProfile::run($user, ($this->profile)(true));

    expect($user->refresh()->date_opted_into_marketing->isToday())->toBeTrue()
        ->and($user->opted_into_marketing)->toBeTrue();

    Bus::assertDispatched(SubscribeToCommunicationListJob::class, fn (SubscribeToCommunicationListJob $job): bool => $job->listId === 'marketing-list'
        && $job->listMember->is($user)
        && $job->afterCommit === true);
    Bus::assertNotDispatched(UnsubscribeFromCommunicationListJob::class);
});

it('does nothing with marketing when a completed profile keeps the same consent', function (bool $optedIn) {
    $optedInAt = now()->subYear()->startOfDay();

    $user = User::factory()->create([
        'profile_step' => User::PROFILE_COMPLETE,
        'opted_into_marketing' => $optedIn,
        'date_opted_into_marketing' => $optedInAt,
    ]);

    CompleteUserProfile::run($user, ($this->profile)($optedIn));

    expect($user->refresh()->date_opted_into_marketing->equalTo($optedInAt))->toBeTrue();

    Bus::assertNotDispatched(SubscribeToCommunicationListJob::class);
    Bus::assertNotDispatched(UnsubscribeFromCommunicationListJob::class);
})->with([
    'opted in' => true,
    'opted out' => false,
]);

it('subscribes and stamps consent when a completed profile opts in', function () {
    $user = User::factory()->create([
        'profile_step' => User::PROFILE_COMPLETE,
        'opted_into_marketing' => false,
    ]);

    CompleteUserProfile::run($user, ($this->profile)(true));

    expect($user->refresh()->opted_into_marketing)->toBeTrue()
        ->and($user->date_opted_into_marketing->isToday())->toBeTrue();

    Bus::assertDispatched(SubscribeToCommunicationListJob::class, fn (SubscribeToCommunicationListJob $job): bool => $job->listMember->is($user)
        && $job->afterCommit === true);
    Bus::assertNotDispatched(UnsubscribeFromCommunicationListJob::class);
});

it('unsubscribes and keeps the original consent date when a user opts out', function (int $profileStep) {
    $optedInAt = now()->subYear()->startOfDay();

    $user = User::factory()->create([
        'profile_step' => $profileStep,
        'opted_into_marketing' => true,
        'date_opted_into_marketing' => $optedInAt,
        'wegive_donor_id' => 'donor-123',
    ]);

    CompleteUserProfile::run($user, ($this->profile)(false));

    expect($user->refresh()->opted_into_marketing)->toBeFalse()
        ->and($user->date_opted_into_marketing->equalTo($optedInAt))->toBeTrue();

    Bus::assertDispatched(UnsubscribeFromCommunicationListJob::class, fn (UnsubscribeFromCommunicationListJob $job): bool => $job->listId === 'marketing-list'
        && $job->donorIds === ['donor-123']
        && $job->user->is($user)
        && $job->afterCommit === true);
    Bus::assertNotDispatched(SubscribeToCommunicationListJob::class);
})->with([
    'completed profile' => User::PROFILE_COMPLETE,
    'first completion' => 1,
]);

it('still dispatches the unsubscribe job when the user has no donor id', function () {
    $user = User::factory()->create([
        'profile_step' => User::PROFILE_COMPLETE,
        'opted_into_marketing' => true,
        'wegive_donor_id' => null,
    ]);

    CompleteUserProfile::run($user, ($this->profile)(false));

    Bus::assertDispatched(UnsubscribeFromCommunicationListJob::class, fn (UnsubscribeFromCommunicationListJob $job): bool => $job->donorIds === [null]);
});

it('dispatches marketing jobs with the updated user', function () {
    $user = User::factory()->create(['profile_step' => 1, 'opted_into_marketing' => false]);

    CompleteUserProfile::run($user, ($this->profile)(true));

    Bus::assertDispatched(SubscribeToCommunicationListJob::class, fn (SubscribeToCommunicationListJob $job): bool => $job->listMember->given_name === 'Jane'
        && $job->listMember->opted_into_marketing === true
        && $job->listMember->isClean());
});

it('stores an uploaded profile photo and removes the previous one', function () {
    Storage::fake('s3');

    Storage::disk('s3')->put('users/old.jpg', 'old');

    $user = User::factory()->create(['image' => 'users/old.jpg', 'profile_step' => User::PROFILE_COMPLETE]);

    CompleteUserProfile::run($user, ($this->profile)(true), UploadedFile::fake()->image('avatar.jpg'));

    $user->refresh();

    expect($user->image)->toStartWith('users/');

    Storage::disk('s3')->assertExists($user->image);
    Storage::disk('s3')->assertMissing('users/old.jpg');
});

it('leaves the profile photo alone when no image is uploaded', function () {
    $user = User::factory()->create(['image' => 'users/existing.jpg']);

    CompleteUserProfile::run($user, ($this->profile)(true));

    expect($user->refresh()->image)->toBe('users/existing.jpg');
});
