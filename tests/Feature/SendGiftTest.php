<?php

use App\Http\Livewire\SendGift;
use App\Mail\Campfire\GiftRequested;
use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('sends a gift request to admin ', function () {
    Mail::fake();
    $campfire = Campfire::factory()->create();

    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company' => 'Test Company',
        'street_address' => '123 Test St',
        'address2' => 'Suite 100',
        'city' => 'Test City',
        'state' => 'NY',
        'zip' => '12345',
        'phone' => '1234567890',
        'country' => 'US',
        'campfire_id' => $campfire->id,
    ];

    livewire(SendGift::class, ['campfire' => $campfire])
        ->set('firstName', $attributes['first_name'])
        ->set('lastName', $attributes['last_name'])
        ->set('company', $attributes['company'])
        ->set('address1', $attributes['street_address'])
        ->set('address2', $attributes['address2'])
        ->set('city', $attributes['city'])
        ->set('state', $attributes['state'])
        ->set('country', $attributes['country'])
        ->set('zip', $attributes['zip'])
        ->set('phone', $attributes['phone'])
        ->call('handle');

    Mail::assertQueued(GiftRequested::class, function ($mail) use ($campfire) {
        return $mail->hasTo($campfire->user->email);
    });

    $this->assertDatabaseHas('addresses', $attributes);
});
