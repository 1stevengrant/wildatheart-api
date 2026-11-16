<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Actions\Event\TransferEventOwnership;

beforeEach(function () {
    $this->oldOwner = User::factory()->create();
    $this->newOwner = User::factory()->create();
    $this->event = Event::factory()->fires()->approved()->for($this->oldOwner)->create();
    $this->event->managers()->attach($this->newOwner->id);

    $this->transfer = EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => $this->newOwner->email,
        'transferred_by_user_id' => $this->oldOwner->id,
    ]);

    Mail::fake();
    Bus::fake();
});

it('swaps the owner and the new owner host role', function () {
    $previousOwner = TransferEventOwnership::run($this->transfer, $this->newOwner);

    $event = $this->event->fresh();

    expect($previousOwner->is($this->oldOwner))->toBeTrue()
        ->and($event->user_id)->toBe($this->newOwner->id)
        ->and($event->managers->modelKeys())->toBe([$this->oldOwner->id]);
});

it('moves the previous owner attendee records to the new owner', function () {
    $ownerAttendee = Attendee::factory()->for($this->event)->for($this->oldOwner, 'user')->create();
    $otherAttendee = Attendee::factory()->for($this->event)->create();

    TransferEventOwnership::run($this->transfer, $this->newOwner);

    expect($ownerAttendee->fresh()->user_id)->toBe($this->newOwner->id)
        ->and($otherAttendee->fresh()->user_id)->not->toBe($this->newOwner->id);
});

it('marks the transfer as accepted', function () {
    TransferEventOwnership::run($this->transfer, $this->newOwner);

    expect($this->transfer->fresh()->isAccepted())->toBeTrue();
});

it('leaves mail and WeGive work to the caller', function () {
    TransferEventOwnership::run($this->transfer, $this->newOwner);

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
    Bus::assertNotDispatched(FindOrCreateDonorJob::class);
});
