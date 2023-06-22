<?php

use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;
use Illuminate\Support\Facades\Mail;

it('send a gift requested email when send gift is submitted', function () {
    Mail::fake();
    $campfire = Campfire::factory()->create();

    \Pest\Livewire\livewire(\App\Http\Livewire\SendGift::class, ['campfire' => $campfire])
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('company', 'Test Company')
        ->set('address1', '123 Test St')
        ->set('address2', 'Suite 100')
        ->set('city', 'Test City')
        ->set('state', 'NY')
        ->set('country', 'US')
        ->set('zip', '12345')
        ->set('phone', '1234567890')
        ->call('handle');

    Mail::assertQueued(\App\Mail\Campfire\GiftRequested::class, function ($mail) use ($campfire) {
        return $mail->hasTo($campfire->user->email);
    });
});
