<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\AttendeeRegistered;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    EmailTemplate::where('slug', 'attendee-registered')->update([
        'body' => 'Hi [host_name], [attendee_name] has expressed interest in your event.',
    ]);
});

it('includes single host name when event has no managers', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $event = Event::factory()->fires()->approved()->for($host)->create();
    $attendee = Attendee::factory()->for($event)->create();

    $mailable = new AttendeeRegistered($event, $attendee);
    $mailable->assertSeeInHtml('Hi John,');
});

it('includes both host names when event has one manager', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $manager = User::factory()->create(['given_name' => 'Jane']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->managers()->attach($manager);
    $event->refresh();

    $attendee = Attendee::factory()->for($event)->create();

    $mailable = new AttendeeRegistered($event, $attendee);
    $mailable->assertSeeInHtml('Hi John and Jane,');
});

it('includes all host names with oxford comma when event has multiple managers', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $manager1 = User::factory()->create(['given_name' => 'Jane']);
    $manager2 = User::factory()->create(['given_name' => 'Bob']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->managers()->attach([$manager1->id, $manager2->id]);
    $event->refresh();

    $attendee = Attendee::factory()->for($event)->create();

    $mailable = new AttendeeRegistered($event, $attendee);
    $mailable->assertSeeInHtml('Hi John, Jane, and Bob,');
});
