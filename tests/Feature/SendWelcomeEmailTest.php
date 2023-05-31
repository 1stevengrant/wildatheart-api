<?php

use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;
use Illuminate\Support\Facades\Mail;

it('sends an email when a new 🔥 is created', function () {
    Mail::fake();
    $campfire = Campfire::factory()->create();

    Mail::assertQueued(SendWelcomeEmail::class, function ($mail) use ($campfire) {
        return $mail->hasTo($campfire->user->email);
    });
});
