<?php

use App\Models\Event;
use Laravel\Pennant\Feature;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Features\NewEventLandingFeature;
use App\Mail\Event\NewEventCreatedMailable;

test('when a published event is created, it sends the new event created email', function () {
    Bus::fake();
    Mail::fake();
    Event::factory()->fires()->approved()->create();
    Mail::assertQueued(NewEventCreatedMailable::class);
});

test('a draft gets the welcome email when it is first published, not when it is created', function () {
    Bus::fake();
    Mail::fake();

    $event = Event::factory()->fires()->create(['approved_at' => null]);
    Mail::assertNotQueued(NewEventCreatedMailable::class);

    $event->update(['approved_at' => now()]);
    Mail::assertQueued(NewEventCreatedMailable::class, 1);
});

test('re-publishing an event that already got its welcome email does not resend it', function () {
    Bus::fake();
    Mail::fake();

    $event = Event::factory()->fires()->create(['approved_at' => null, 'welcome_sent_at' => now()]);
    $event->update(['approved_at' => now()]);

    Mail::assertNotQueued(NewEventCreatedMailable::class);
});

test('it does not break link text mid-word', function () {
    Bus::fake();
    Mail::fake();

    EmailTemplate::where('slug', 'send-fires-welcome-email')->firstOrFail()->update([
        'body' => "Review the [facilitator's guide](https://example.com/becoming-a-king-retreat/event-guide).",
    ]);

    $event = Event::factory()->fires()->create();

    $html = (new NewEventCreatedMailable($event))->render();

    expect($html)->not->toContain('word-break: break-all');
});

test('it sends from the sender set on the CMS template', function () {
    Bus::fake();
    Mail::fake();

    EmailTemplate::where('slug', 'send-fires-welcome-email')->firstOrFail()->update([
        'from_name' => 'Wild at Heart Local',
        'from_email' => 'local@wildatheart.org',
    ]);

    $mailable = new NewEventCreatedMailable(Event::factory()->fires()->create());

    $mailable->assertFrom('local@wildatheart.org', 'Wild at Heart Local');
});

test('it falls back to the default sender when the template sets none', function () {
    Bus::fake();
    Mail::fake();

    EmailTemplate::where('slug', 'send-fires-welcome-email')->firstOrFail()->update([
        'from_name' => null,
        'from_email' => null,
    ]);

    $mailable = new NewEventCreatedMailable(Event::factory()->fires()->create());

    expect($mailable->envelope()->from)->toBeNull();
});

test('it loads the header logo from the configured public URL, not APP_URL', function () {
    Bus::fake();
    Mail::fake();
    config([
        'app.url' => 'local.wildatheart.test',
        'mail.logo_url' => 'https://cdn.example.test/email-logo.png',
    ]);

    $html = (new NewEventCreatedMailable(Event::factory()->fires()->create()))->render();

    expect($html)->toContain('src="https://cdn.example.test/email-logo.png"')
        ->not->toContain('src="http://localhost');
});

test('it links to the listing on the frontend when landing pages are site-relative', function () {
    Bus::fake();
    Mail::fake();
    config(['app.frontend_url' => 'https://www.wildatheart.org/']);
    Feature::activate(NewEventLandingFeature::class);

    EmailTemplate::where('slug', 'send-fires-welcome-email')->firstOrFail()->update([
        'body' => 'Here is [your listing]([event_url]).',
    ]);

    $event = Event::factory()->fires()->create();

    $html = (new NewEventCreatedMailable($event))->render();

    expect($html)->toContain("https://www.wildatheart.org/events/attend/{$event->id}");
});
