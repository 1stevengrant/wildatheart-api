<?php

use App\Actions\User\SendVerificationEmail;
use App\Mail\Campfire\SendEmailVerificationEmail;
use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
it('sends a verification email', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $campfire = Campfire::factory()->create();
    $this->actingAs($campfire->host);

    SendVerificationEmail::run($campfire);

    Mail::assertQueued(SendEmailVerificationEmail::class, function ($mail) use ($campfire) {
        return $mail->hasTo($campfire->host->email);
    });
});
