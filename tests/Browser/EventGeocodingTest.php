<?php

use App\Models\Event;

$blockNextSave = <<<'JS'
    () => {
        window.submittedEventBody = null;

        const send = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.send = function (body) {
            XMLHttpRequest.prototype.send = send;
            window.submittedEventBody = body;
            this.abort();
        };

        return true;
    }
    JS;

$reverseGeocoding = <<<'JS'
    (behaviour) => {
        const fetch = window.fetch;
        let lookups = 0;

        window.fetch = (input, init) => {
            const url = String(input);

            if (! /mapbox\.places\/-?[\d.]+,-?[\d.]+\.json/.test(url)) {
                return fetch(input, init);
            }

            lookups += 1;

            if (behaviour === 'fail') {
                return Promise.resolve(new Response('', { status: 500 }));
            }

            if (behaviour === 'slow-first' && lookups === 1) {
                return new Promise((resolve) => setTimeout(resolve, 2500)).then(() => fetch(input, init));
            }

            return fetch(input, init);
        };

        return true;
    }
    JS;

function activeFire(): Event
{
    // Browser hits wahfires.test, which is served against the local database.
    $event = Event::on('local')->published()->active()->where('event_type_id', 1)->first();

    if (! $event) {
        test()->markTestSkipped('No active fire found in local database');
    }

    return $event;
}

it('keeps the latest searched place when an earlier lookup finishes last', function () use ($blockNextSave, $reverseGeocoding) {
    $event = activeFire();

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/{$event->id}");

    $page->assertPresent('#event_location');
    $page->script("({$reverseGeocoding})('slow-first')");

    $page->type('#event_location', 'Wellington, New Zealand');
    $page->assertPresent('[role="option"]');
    $page->script('() => document.querySelector(\'[role="option"]\').click()');

    $page->clear('#event_location');
    $page->type('#event_location', 'Auckland, New Zealand');
    $page->assertPresent('[role="option"]');
    $page->script('() => document.querySelector(\'[role="option"]\').click()');
    $page->wait(4);

    $page->script($blockNextSave);
    $page->click('button:has-text("Update This")');
    $page->wait(1);

    $submitted = json_decode($page->script('() => window.submittedEventBody'), true);

    expect((float) $submitted['latitude'])->toEqualWithDelta(-36.85, 0.2)
        ->and($submitted['city'])->toBe('Auckland');
});

it('tells the host when the location lookup fails and keeps the saved location', function () use ($blockNextSave, $reverseGeocoding) {
    $event = activeFire();

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/{$event->id}");

    $page->assertPresent('#event_location');
    $page->script("({$reverseGeocoding})('fail')");

    $page->type('#event_location', 'Wellington, New Zealand');
    $page->assertPresent('[role="option"]');
    $page->script('() => document.querySelector(\'[role="option"]\').click()');

    $page->assertSee("We couldn't look up that location");

    $page->script($blockNextSave);
    $page->click('button:has-text("Update This")');
    $page->wait(1);

    $submitted = json_decode($page->script('() => window.submittedEventBody'), true);

    expect((float) $submitted['latitude'])->toEqualWithDelta((float) $event->latitude, 0.0001);
});
