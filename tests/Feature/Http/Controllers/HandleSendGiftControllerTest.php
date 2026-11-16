<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Address;
use App\Jobs\PostToZapier;
use App\Mail\Event\GiftRequested;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->event = Event::factory()->fires()->create();

    $this->addressData = [
        'country' => 'US',
        'given_name' => 'John',
        'family_name' => 'Doe',
        'company' => 'Test Company',
        'street_address' => '123 Main St',
        'address2' => 'Suite 100',
        'city' => 'Anytown',
        'state' => 'CA',
        'zip' => '12345',
        'phone' => '1234567890',
    ];
});

test('it creates an address for the event that can be shipped to', function () {
    $response = $this->actingAs($this->event->user)
        ->post(route('gift.store', $this->event), $this->addressData);

    $response->assertRedirect(route('my.events.active'));

    $this->assertDatabaseHas('addresses',
        array_merge($this->addressData, ['event_id' => $this->event->id, 'is_primary' => true])
    );
});

it('marks the gift as requested', function () {
    $this->actingAs($this->event->user)
        ->post(route('gift.store', $this->event), $this->addressData);

    expect($this->event->fresh()->gift_requested)->toBeTrue();
});

it('sends mail notification to admin about the request', function () {
    $this->actingAs($this->event->user)
        ->post(route('gift.store', $this->event), $this->addressData);

    Mail::assertQueued(GiftRequested::class);
});

it('updates zapier about the request', function () {
    $this->actingAs($this->event->user)
        ->post(route('gift.store', $this->event), $this->addressData);

    Bus::assertDispatched(PostToZapier::class);
});

it('forbids a user who does not own the event', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('gift.store', $this->event), $this->addressData)
        ->assertForbidden();

    expect($this->event->fresh()->gift_requested)->toBeFalse();
    $this->assertDatabaseMissing('addresses', ['event_id' => $this->event->id]);
    Mail::assertNotQueued(GiftRequested::class);
    Bus::assertNotDispatched(PostToZapier::class);
});

it('forbids a co-host of the event', function () {
    $manager = User::factory()->create();
    $this->event->managers()->attach($manager);

    $this->actingAs($manager)
        ->post(route('gift.store', $this->event), $this->addressData)
        ->assertForbidden();

    expect($this->event->fresh()->gift_requested)->toBeFalse();
});

it('processes a repeated submission only once', function () {
    $this->actingAs($this->event->user)
        ->post(route('gift.store', $this->event), $this->addressData)
        ->assertRedirect(route('my.events.active'))
        ->assertSessionHas('notifications.message', 'Your free gift has been requested!');

    $this->actingAs($this->event->user)
        ->post(route('gift.store', $this->event), $this->addressData)
        ->assertForbidden();

    expect(Address::query()->where('event_id', $this->event->id)->count())->toBe(1);
    Mail::assertQueued(GiftRequested::class, 1);
    Bus::assertDispatchedTimes(PostToZapier::class, 1);
});
