<?php

use App\Models\User;
use App\Models\Event;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->coHost = User::factory()->create();
    $this->event = Event::factory()->fires()->approved()->for($this->owner)->create();
    $this->event->managers()->attach($this->coHost->id);

    Mail::fake();
    Bus::fake();
});

it('flashes a success message after initiating a transfer', function () {
    actingAs($this->owner)
        ->from('/previous')
        ->post(route('events.ownership.store', $this->event), ['email' => $this->coHost->email])
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => "Ownership transfer invitation has been sent to {$this->coHost->fullname}.",
            'type' => 'success',
        ]);
});

it('flashes an error when a co-host tries to transfer ownership', function () {
    actingAs($this->coHost)
        ->from('/previous')
        ->post(route('events.ownership.store', $this->event), ['email' => $this->coHost->email])
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => 'Only the event owner can transfer ownership.',
            'type' => 'error',
        ]);

    expect(EventOwnershipTransfer::query()->count())->toBe(0);
});

it('flashes a success message after cancelling a transfer', function () {
    EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => $this->coHost->email,
        'transferred_by_user_id' => $this->owner->id,
    ]);

    actingAs($this->owner)
        ->from('/previous')
        ->delete(route('events.ownership.destroy', $this->event))
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => "Ownership transfer to {$this->coHost->email} has been cancelled.",
            'type' => 'success',
        ]);
});

it('redirects the new owner to the event after accepting', function () {
    $transfer = EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => $this->coHost->email,
        'transferred_by_user_id' => $this->owner->id,
    ]);

    actingAs($this->coHost)
        ->post(route('accept.ownership.transfer.confirm', $transfer))
        ->assertRedirect(route('my.events.edit', $this->event))
        ->assertSessionHas('notifications', [
            'message' => "You are now the owner of {$this->event->name}!",
            'type' => 'success',
        ]);

    expect($this->event->fresh()->user_id)->toBe($this->coHost->id);
});

it('redirects home when a different user tries to accept', function () {
    $transfer = EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => $this->coHost->email,
        'transferred_by_user_id' => $this->owner->id,
    ]);

    actingAs(User::factory()->create())
        ->post(route('accept.ownership.transfer.confirm', $transfer))
        ->assertRedirect(route('home'))
        ->assertSessionHas('notifications', [
            'message' => 'This ownership transfer is for a different email address.',
            'type' => 'error',
        ]);

    expect($this->event->fresh()->user_id)->toBe($this->owner->id);
});
