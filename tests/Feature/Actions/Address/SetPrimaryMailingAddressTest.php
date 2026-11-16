<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Data\MailingAddressData;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Actions\Address\SetPrimaryMailingAddress;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->mailingAddress = new MailingAddressData(
        given_name: 'Steven',
        family_name: 'Grant',
        street_address: '18 Greenlees Road',
        city: 'Glasgow',
        state: 'Lanarkshire',
        zip: 'G72 8JJ',
        country: 'UK',
        company: null,
        address2: null,
        phone: '079 1234 5678',
    );
});

it('creates a primary address for the user', function () {
    $user = User::factory()->create();

    $address = SetPrimaryMailingAddress::run($user, $this->mailingAddress);

    expect($address->is_primary)->toBeTrue()
        ->and($address->user_id)->toBe($user->id)
        ->and($address->event_id)->toBeNull();

    $this->assertDatabaseHas('addresses', [
        ...$this->mailingAddress->toArray(),
        'user_id' => $user->id,
        'is_primary' => true,
    ]);
});

it('links the address to an event when one is given', function () {
    $event = Event::factory()->fires()->create();

    $address = SetPrimaryMailingAddress::run($event->user, $this->mailingAddress, $event);

    expect($address->event_id)->toBe($event->id);
});

it('demotes the user\'s existing primary addresses', function () {
    $user = User::factory()->create();
    $previous = Address::factory()->for($user)->create(['is_primary' => true]);
    $otherUsersAddress = Address::factory()->for(User::factory())->create(['is_primary' => true]);

    $address = SetPrimaryMailingAddress::run($user, $this->mailingAddress);

    expect($previous->fresh()->is_primary)->toBeFalse()
        ->and($otherUsersAddress->fresh()->is_primary)->toBeTrue()
        ->and($user->addresses()->where('is_primary', true)->pluck('id')->all())->toBe([$address->id]);
});

it('fills the user\'s name when either part is missing', function () {
    $user = User::factory()->create(['given_name' => 'Kept', 'family_name' => null]);

    SetPrimaryMailingAddress::run($user, $this->mailingAddress);

    expect($user->fresh())
        ->given_name->toBe('Steven')
        ->family_name->toBe('Grant')
        ->name->toBe('Steven Grant');
});

it('leaves a complete user name alone', function () {
    $user = User::factory()->create(['given_name' => 'Jane', 'family_name' => 'Doe', 'name' => 'Jane Doe']);

    SetPrimaryMailingAddress::run($user, $this->mailingAddress);

    expect($user->fresh())
        ->given_name->toBe('Jane')
        ->family_name->toBe('Doe')
        ->name->toBe('Jane Doe');
});

it('rolls back the demotion when the new address cannot be created', function () {
    $user = User::factory()->create();
    $previous = Address::factory()->for($user)->create(['is_primary' => true]);

    Address::creating(fn () => throw new RuntimeException('Address insert failed'));

    expect(fn () => SetPrimaryMailingAddress::run($user, $this->mailingAddress))
        ->toThrow(RuntimeException::class);

    expect($previous->fresh()->is_primary)->toBeTrue()
        ->and($user->addresses()->count())->toBe(1);
});
