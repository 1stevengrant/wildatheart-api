<?php

use App\Models\Event;
use App\Models\EventType;

$recordSubmissions = <<<'JS'
    () => {
        window.submittedBodies = [];

        const send = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.send = function (body) {
            window.submittedBodies.push(body);
            this.abort();
        };

        return true;
    }
    JS;

it('does not save the event when the host presses Enter in the location search', function () use ($recordSubmissions) {
    // Browser hits wahfires.test, which is served against the local database.
    $event = Event::on('local')->published()->active()->where('event_type_id', 1)->first();

    if (! $event) {
        $this->markTestSkipped('No active fire found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/{$event->id}");

    $page->assertPresent('#event_location');
    $page->script($recordSubmissions);

    $page->keys('#event_location', ['Enter']);
    $page->wait(1);

    expect($page->script('() => window.submittedBodies'))->toBe([]);
});

it('keeps the create form when the host presses Enter in the location search', function () use ($recordSubmissions) {
    $eventType = EventType::on('local')
        ->active()
        ->whereNotIn('name', [EventType::FIRES, EventType::CAPTIVATING_FIRE])
        ->first();

    $host = Event::on('local')->where('event_type_id', '!=', 1)->latest()->value('user_id');

    if (! $eventType || ! $host) {
        $this->markTestSkipped('No scheduled event type or host found in local database');
    }

    // The guide page sets up the step progress the create page reads from the session.
    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$host}&redirect=/host/{$eventType->slug}")
        ->navigate("https://wahfires.test/host/{$eventType->slug}/create");

    $page->assertPresent('#event_location');
    $page->script('() => { window.notReloaded = true; return true; }');
    $page->script($recordSubmissions);

    $page->keys('#event_location', ['Enter']);
    $page->wait(1);

    expect($page->script('() => window.notReloaded === true'))->toBeTrue()
        ->and($page->script('() => window.submittedBodies'))->toBe([]);
});
