<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable;

beforeEach(function () {
    Mail::fake();
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    EmailTemplate::where('slug', 'monthly-fire-cleanup-email-to-host')->update([
        'body' => 'Hi [event_host_name], please confirm your fire is still active.',
    ]);
});

it('includes single host name when event has no managers', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $event = Event::factory()->fires()->approved()->for($host)->create();

    $mailable = new MonthlyFireCleanupEmailToHostMailable($event);
    $mailable->assertSeeInHtml('Hi John,');
});

it('includes both host names when event has one manager', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $manager = User::factory()->create(['given_name' => 'Jane']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->managers()->attach($manager);
    $event->refresh();

    $mailable = new MonthlyFireCleanupEmailToHostMailable($event);
    $mailable->assertSeeInHtml('Hi John and Jane,');
});

it('includes all host names with oxford comma when event has multiple managers', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $manager1 = User::factory()->create(['given_name' => 'Jane']);
    $manager2 = User::factory()->create(['given_name' => 'Bob']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->managers()->attach([$manager1->id, $manager2->id]);
    $event->refresh();

    $mailable = new MonthlyFireCleanupEmailToHostMailable($event);
    $mailable->assertSeeInHtml('Hi John, Jane, and Bob,');
});
