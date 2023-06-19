<?php

use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;
use Illuminate\Support\Facades\Mail;

it('a welcome email is sent when a user lands on the gift page', function () {
    Mail::fake();
    $campfire = Campfire::factory()->create();

    $this->get(route('gift', $campfire));

    Mail::assertQueued(SendWelcomeEmail::class, function ($mail) use ($campfire) {
        return $mail->hasTo($campfire->user->email);
    });
});
