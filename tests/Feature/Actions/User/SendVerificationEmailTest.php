<?php

use App\Actions\User\SendVerificationEmail;
use App\Mail\Event\SendEmailVerificationEmail;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(\Database\Seeders\UserSeeder::class);
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
});
it('sends a verification email', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $event = Event::factory()->fires()->create();

    $this->actingAs($event->host);

    SendVerificationEmail::run($event);

    Mail::assertQueued(SendEmailVerificationEmail::class, function ($mail) use ($event) {
        return $mail->hasTo($event->host->email);
    });
});
