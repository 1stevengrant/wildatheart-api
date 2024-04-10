<?php

use App\Jobs\PostToZapier;
use App\Mail\Event\GiftRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\UserSeeder::class);
    $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->create();

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
    $response = $this->actingAs($this->event->host)
        ->post(route('gift.store', $this->event), $this->addressData);

    $response->assertRedirect(route('gift.success', $this->event));

    $this->assertDatabaseHas('addresses',
        array_merge($this->addressData, ['event_id' => $this->event->id, 'is_primary' => true])
    );
});

it('marks the gift as requested', function () {
    $this->actingAs($this->event->host)
        ->post(route('gift.store', $this->event), $this->addressData);

    expect($this->event->fresh()->gift_requested)->toBe(true);
});

it('sends mail notification to admin about the request', function () {
    Mail::fake();
    $this->actingAs($this->event->host)
        ->post(route('gift.store', $this->event), $this->addressData);

    Mail::assertQueued(GiftRequested::class);
});

it('updates zapier about the request', function () {
    Queue::fake();
    $this->actingAs($this->event->host)
        ->post(route('gift.store', $this->event), $this->addressData);

    Queue::assertPushed(PostToZapier::class);

});

it('redirects to a success page', function () {

    $response = $this->actingAs($this->event->host)
        ->post(route('gift.store', $this->event), $this->addressData);

    $response->assertRedirect(route('gift.success', $this->event));

});
