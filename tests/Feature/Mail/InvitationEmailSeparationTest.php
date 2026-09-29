<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Models\EventOwnershipTransfer;
use App\Mail\V1\HostInvitationMail as V1HostInvitationMail;
use App\Mail\HostInvitationMail as LegacyHostInvitationMail;
use App\Mail\V1\OwnershipTransferInvitationMail as V1OwnershipTransferInvitationMail;
use App\Mail\OwnershipTransferInvitationMail as LegacyOwnershipTransferInvitationMail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    config(['app.frontend_url' => 'https://frontend.test/']);

    $this->event = Event::factory()->fires()->approved()->create(['name' => "Test's"]);
    $this->invitation = EventManagerInvitation::create([
        'event_id' => $this->event->id,
        'email' => 'cohost@example.test',
        'invited_by_user_id' => $this->event->user_id,
    ]);
    $this->transfer = EventOwnershipTransfer::create([
        'event_id' => $this->event->id,
        'email' => 'owner@example.test',
        'transferred_by_user_id' => $this->event->user_id,
    ]);
});

it('keeps legacy host invitations on the legacy acceptance page', function () {
    $mail = new LegacyHostInvitationMail($this->event, 'Nic', $this->invitation);

    expect($mail->render())
        ->toContain(route('accept.host.invitation', $this->invitation))
        ->not->toContain('frontend.test')
        ->and($mail->envelope()->subject)->toBe("You've been invited to host Test's");
});

it('links v1 host invitations to the frontend acceptance page', function () {
    $mail = new V1HostInvitationMail($this->event, 'Nic', $this->invitation);

    expect($mail->render())
        ->toContain("https://frontend.test/accept-host-invitation/{$this->invitation->id}")
        ->not->toContain(route('accept.host.invitation', $this->invitation))
        ->and($mail->envelope()->subject)->toBe("You've been invited to host Test's Fire");
});

it('keeps legacy ownership transfer invitations on the legacy acceptance page', function () {
    $mail = new LegacyOwnershipTransferInvitationMail($this->event, 'Nic', $this->transfer, true);

    expect($mail->render())
        ->toContain(route('accept.ownership.transfer', $this->transfer))
        ->not->toContain('frontend.test')
        ->and($mail->envelope()->subject)->toBe("You've been invited to take ownership of Test's");
});

it('links v1 ownership transfer invitations to the frontend acceptance page', function () {
    $mail = new V1OwnershipTransferInvitationMail($this->event, 'Nic', $this->transfer);

    expect($mail->render())
        ->toContain("https://frontend.test/accept-ownership-transfer/{$this->transfer->id}")
        ->not->toContain(route('accept.ownership.transfer', $this->transfer))
        ->and($mail->envelope()->subject)->toBe("You've been invited to take ownership of Test's Fire");
});

it('sends the legacy invitation when a co-host is added from the legacy dashboard', function () {
    $event = Event::factory()->fires()->approved()->create();

    $this->actingAs($event->user)
        ->post(route('events.managers.store', $event), ['email' => 'new-cohost@example.test']);

    Mail::assertQueued(LegacyHostInvitationMail::class, fn ($mail) => $mail->hasTo('new-cohost@example.test'));
    Mail::assertNotQueued(V1HostInvitationMail::class);
});

it('sends the legacy invitation when ownership is transferred from the legacy dashboard', function () {
    $event = Event::factory()->fires()->approved()->create();
    $coHost = User::factory()->create();
    $event->managers()->attach($coHost->id);

    $this->actingAs($event->user)
        ->post(route('events.ownership.store', $event), ['email' => $coHost->email]);

    Mail::assertQueued(LegacyOwnershipTransferInvitationMail::class, fn ($mail) => $mail->hasTo($coHost->email));
    Mail::assertNotQueued(V1OwnershipTransferInvitationMail::class);
});
