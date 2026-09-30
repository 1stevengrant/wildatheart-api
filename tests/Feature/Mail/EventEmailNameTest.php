<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Mail\V1\OwnershipTransferInvitationMail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('names fires the way the site displays them', function () {
    expect(Event::factory()->fires()->create(['name' => "Test's"])->emailName())->toBe("Test's Fire")
        ->and(Event::factory()->captivatingFires()->create(['name' => "Test's"])->emailName())
        ->toBe("Test's Captivating Fire");
});

it('keeps the stored name for other event types', function () {
    expect(Event::factory()->wahRetreat()->create(['name' => 'Woodland Park, CO'])->emailName())
        ->toBe('Woodland Park, CO');
});

it('puts the full fire name in the ownership transfer subject', function () {
    $event = Event::factory()->fires()->create(['name' => "Test's"]);
    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => 'owner@example.test',
        'transferred_by_user_id' => $event->user_id,
    ]);

    expect((new OwnershipTransferInvitationMail($event, 'Nic Bovee', $transfer))->envelope()->subject)
        ->toBe("You've been invited to take ownership of Test's Fire");
});
