<?php

use App\Models\User;
use App\Models\Event;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Mail\OwnershipTransferInvitationMail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->owner = User::factory()->create();
    $this->coHost = User::factory()->create();
    $this->event = Event::factory()->fires()->approved()->for($this->owner)->create();
    $this->event->managers()->attach($this->coHost->id);
});

it('lets the owner start a transfer to a co-host', function () {
    $this->actingAs($this->owner)
        ->post(route('events.ownership.store', $this->event), ['email' => $this->coHost->email])
        ->assertRedirect()
        ->assertSessionHas('notifications.type', 'success');

    expect(EventOwnershipTransfer::where('event_id', $this->event->id)->value('email'))->toBe($this->coHost->email);
    Mail::assertQueued(OwnershipTransferInvitationMail::class, fn ($mail) => $mail->hasTo($this->coHost->email));
});

it('forbids an admin who is not the owner from starting a transfer', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRoleEnum::Admin->value);

    $this->actingAs($admin)
        ->post(route('events.ownership.store', $this->event), ['email' => $this->coHost->email])
        ->assertForbidden();

    expect(EventOwnershipTransfer::where('event_id', $this->event->id)->exists())->toBeFalse();
    Mail::assertNotQueued(OwnershipTransferInvitationMail::class);
});

it('forbids a co-host from starting a transfer', function () {
    $otherCoHost = User::factory()->create();
    $this->event->managers()->attach($otherCoHost->id);

    $this->actingAs($this->coHost)
        ->post(route('events.ownership.store', $this->event), ['email' => $otherCoHost->email])
        ->assertForbidden();

    expect(EventOwnershipTransfer::where('event_id', $this->event->id)->exists())->toBeFalse();
});

it('flashes an error when the new owner is not a co-host', function () {
    $stranger = User::factory()->create();

    $this->actingAs($this->owner)
        ->post(route('events.ownership.store', $this->event), ['email' => $stranger->email])
        ->assertRedirect()
        ->assertSessionHas('notifications.message', 'Only existing hosts can be promoted to owner. Please add them as a host first.');

    Mail::assertNotQueued(OwnershipTransferInvitationMail::class);
});

it('lets the owner cancel a pending transfer', function () {
    EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => $this->coHost->email,
        'transferred_by_user_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->delete(route('events.ownership.destroy', $this->event))
        ->assertRedirect()
        ->assertSessionHas('notifications.type', 'success');

    expect(EventOwnershipTransfer::where('event_id', $this->event->id)->exists())->toBeFalse();
});

it('forbids an admin who is not the owner from cancelling a transfer', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRoleEnum::Admin->value);

    EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => $this->coHost->email,
        'transferred_by_user_id' => $this->owner->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('events.ownership.destroy', $this->event))
        ->assertForbidden();

    expect(EventOwnershipTransfer::where('event_id', $this->event->id)->exists())->toBeTrue();
});
