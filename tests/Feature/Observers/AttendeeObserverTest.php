<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\AttendeeRegistered;
use App\Mail\Event\AttendeeConnectedToHost;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('sends email to primary host when attendee is created with no managers', function () {
    $host = User::factory()->create(['email' => 'host@example.com']);
    $event = Event::factory()->fires()->approved()->for($host)->create();

    $attendee = Attendee::factory()->for($event)->create();

    Mail::assertQueued(AttendeeRegistered::class, function ($mail) use ($host) {
        return $mail->hasTo($host->email);
    });
});

it('sends email to primary host with manager CC\'d when attendee is created with one manager', function () {
    $host = User::factory()->create(['email' => 'host@example.com']);
    $manager = User::factory()->create(['email' => 'manager@example.com']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->managers()->attach($manager);
    $event->refresh();

    $attendee = Attendee::factory()->for($event)->create();

    Mail::assertQueued(AttendeeRegistered::class, function ($mail) use ($host, $manager) {
        return $mail->hasTo($host->email) &&
               $mail->hasCc($manager->email);
    });
});

it('sends email to primary host with all managers CC\'d when attendee is created with multiple managers', function () {
    $host = User::factory()->create(['email' => 'host@example.com']);
    $manager1 = User::factory()->create(['email' => 'manager1@example.com']);
    $manager2 = User::factory()->create(['email' => 'manager2@example.com']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->managers()->attach([$manager1->id, $manager2->id]);
    $event->refresh();

    $attendee = Attendee::factory()->for($event)->create();

    Mail::assertQueued(AttendeeRegistered::class, function ($mail) use ($host, $manager1, $manager2) {
        return $mail->hasTo($host->email) &&
               $mail->hasCc($manager1->email) &&
               $mail->hasCc($manager2->email);
    });
});

it('sends attendee connected email when attendee is created', function () {
    $host = User::factory()->create(['email' => 'host@example.com']);
    $event = Event::factory()->fires()->approved()->for($host)->create();

    $attendee = Attendee::factory()->for($event)->create(['email' => 'attendee@example.com']);

    Mail::assertQueued(AttendeeConnectedToHost::class, function ($mail) use ($attendee) {
        return $mail->hasTo($attendee->email);
    });
});
