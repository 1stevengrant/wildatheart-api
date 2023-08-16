<?php

use App\Http\Livewire\SendGift;
use App\Mail\Campfire\GiftRequested;
use App\Models\Campfire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);
it('sends a gift request email', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $user = User::factory()->create();
    $campfire = Campfire::factory()->create([
        'gift_requested' => false,
        'user_id' => $user->id,
    ]);

    expect($campfire->gift_requested)->toBeFalse();

    livewire(SendGift::class, ['campfire' => $campfire])
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('country', 'US')
        ->set('address1', '123 Main St')
        ->set('city', 'Anytown')
        ->set('state', 'NY')
        ->set('zip', '12345')
        ->set('phone', '5555555555')
        ->call('handle');

    $campfire->refresh();

    Mail::assertQueued(GiftRequested::class);
});
