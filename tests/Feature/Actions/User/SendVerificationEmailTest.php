<?php

use App\Actions\User\SendVerificationEmail;
use App\Mail\Event\SendEmailVerificationEmail;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
it('sends a verification email', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $event = Event::factory()->create();
    $this->actingAs($event->host);

    SendVerificationEmail::run($event);

    Mail::assertQueued(SendEmailVerificationEmail::class, function ($mail) use ($event) {
        return $mail->hasTo($event->host->email);
    });
});
