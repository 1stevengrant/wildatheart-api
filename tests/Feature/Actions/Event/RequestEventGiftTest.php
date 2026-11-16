<?php

use App\Models\Event;
use App\Models\Address;
use App\Jobs\PostToZapier;
use App\Data\MailingAddressData;
use App\Mail\Event\GiftRequested;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Actions\Event\RequestEventGift;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->event = Event::factory()->fires()->create();

    Bus::fake();
    Mail::fake();

    $this->mailingAddress = new MailingAddressData(
        given_name: 'John',
        family_name: 'Doe',
        street_address: '123 Main St',
        city: 'Anytown',
        state: 'CA',
        zip: '12345',
        country: 'US',
        company: 'Test Company',
        address2: 'Suite 100',
        phone: '1234567890',
    );
});

it('saves the address and marks the gift as requested', function () {
    RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress);

    expect($this->event->fresh()->gift_requested)->toBeTrue();

    $this->assertDatabaseHas('addresses', [
        ...$this->mailingAddress->toArray(),
        'user_id' => $this->event->user_id,
        'event_id' => $this->event->id,
        'is_primary' => true,
    ]);
});

it('sends the mail and the zapier payload exactly once', function () {
    RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress);

    Mail::assertQueuedCount(1);
    Mail::assertQueued(GiftRequested::class, fn (GiftRequested $mail) => $mail->hasTo($this->event->user->email)
        && $mail->event->is($this->event));

    Bus::assertDispatchedTimes(PostToZapier::class, 1);
    Bus::assertDispatched(PostToZapier::class, function (PostToZapier $job) {
        $payload = $job->dataTransferObject;

        return $payload->webhookUrl === $this->event->eventType->zapier_webhook_url
            && $payload->eventId === $this->event->id
            && $payload->eventType === $this->event->eventType->name
            && $payload->email === $this->event->user->email
            && $payload->quantity === '1'
            && $payload->first_name === 'John'
            && $payload->last_name === 'Doe'
            && $payload->address_1 === '123 Main St'
            && $payload->address_2 === 'Suite 100'
            && $payload->city === 'Anytown'
            && $payload->state === 'CA'
            && $payload->zip === '12345'
            && $payload->country === 'US'
            && $payload->date === now()->toDateString();
    });
});

it('rejects a second request for the same event', function () {
    RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress);

    expect(fn () => RequestEventGift::run($this->event->user, $this->event->fresh(), $this->mailingAddress))
        ->toThrow(HttpException::class, 'You have already requested your gift.');

    expect(Address::query()->where('event_id', $this->event->id)->count())->toBe(1);
    Mail::assertQueuedCount(1);
    Bus::assertDispatchedTimes(PostToZapier::class, 1);
});

it('rejects a concurrent request holding a stale copy of the event', function () {
    $staleEvent = Event::query()->findOrFail($this->event->id);

    RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress);

    expect($staleEvent->gift_requested)->toBeFalse();

    expect(fn () => RequestEventGift::run($this->event->user, $staleEvent, $this->mailingAddress))
        ->toThrow(HttpException::class, 'You have already requested your gift.');

    expect(Address::query()->where('event_id', $this->event->id)->count())->toBe(1);
    Mail::assertQueuedCount(1);
    Bus::assertDispatchedTimes(PostToZapier::class, 1);
});

it('rolls back the address and sends nothing when marking the gift fails', function () {
    $previous = Address::factory()->for($this->event->user)->create(['is_primary' => true]);

    Event::updating(fn () => throw new RuntimeException('Event update failed'));

    expect(fn () => RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress))
        ->toThrow(RuntimeException::class);

    expect($this->event->fresh()->gift_requested)->toBeFalse()
        ->and($previous->fresh()->is_primary)->toBeTrue()
        ->and(Address::query()->where('event_id', $this->event->id)->exists())->toBeFalse();

    Mail::assertNothingQueued();
    Bus::assertNotDispatched(PostToZapier::class);
});

it('waits for the surrounding transaction to commit before sending', function () {
    DB::beginTransaction();

    RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress);

    Mail::assertNothingQueued();
    Bus::assertNotDispatched(PostToZapier::class);

    DB::commit();

    Mail::assertQueuedCount(1);
    Bus::assertDispatchedTimes(PostToZapier::class, 1);
});

it('sends nothing when the surrounding transaction rolls back', function () {
    DB::beginTransaction();

    RequestEventGift::run($this->event->user, $this->event, $this->mailingAddress);

    DB::rollBack();

    expect($this->event->fresh()->gift_requested)->toBeFalse();
    Mail::assertNothingQueued();
    Bus::assertNotDispatched(PostToZapier::class);
});
