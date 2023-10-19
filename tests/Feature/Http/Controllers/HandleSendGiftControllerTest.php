<?php

use App\Jobs\PostToZapier;
use App\Mail\Campfire\GiftRequested;
use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->campfire = Campfire::factory()->create();

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

test('it creates an address for the fire that can be shipped to', function () {
    $response = $this->actingAs($this->campfire->host)
        ->post(route('gift.store', $this->campfire), $this->addressData);

    $response->assertRedirect(route('gift.success', $this->campfire));

    $this->assertDatabaseHas('addresses',
        array_merge($this->addressData, ['campfire_id' => $this->campfire->id, 'is_primary' => true])
    );
});

it('marks the gift as requested', function () {
    $this->actingAs($this->campfire->host)
        ->post(route('gift.store', $this->campfire), $this->addressData);

    expect($this->campfire->fresh()->gift_requested)->toBe(1);
});

it('sends mail notification to admin about the request', function () {
    Mail::fake();
    $this->actingAs($this->campfire->host)
        ->post(route('gift.store', $this->campfire), $this->addressData);

    Mail::assertQueued(GiftRequested::class);
});

it('updates zapier about the request', function () {
    Queue::fake();
    $this->actingAs($this->campfire->host)
        ->post(route('gift.store', $this->campfire), $this->addressData);

    Queue::assertPushed(PostToZapier::class);

});

it('redirects to a success page', function () {

    $response = $this->actingAs($this->campfire->host)
        ->post(route('gift.store', $this->campfire), $this->addressData);

    $response->assertRedirect(route('gift.success', $this->campfire));

});
