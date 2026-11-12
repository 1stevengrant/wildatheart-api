<?php

use App\Models\Event;

it('saves the event date from the edit form without filtering the home map by it', function () {
    // Browser hits wahfires.test, which is served against the local database.
    $event = Event::on('local')
        ->published()
        ->active()
        ->where('event_type_id', '!=', 1)
        ->where('start_date', '>', now()->addMonth())
        ->first();

    if (! $event) {
        $this->markTestSkipped('No upcoming scheduled event found in local database');
    }

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

    $startDate = $event->start_date;
    $newStartDate = $startDate->copy()->addDays(2);

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/{$event->id}");

    $page->assertSee($startDate->format('F j, Y'));

    $page->click("text={$startDate->format('F j, Y')}");
    $page->click("button[aria-label=\"{$newStartDate->format('j F Y')}\"]");
    $page->assertSee($newStartDate->format('F j, Y'));

    $page->script($blockNextSave);
    $page->click('button:has-text("Update This")');
    $page->wait(1);

    $submittedEvent = json_decode($page->script('() => window.submittedEventBody'), true);

    expect($submittedEvent['start_date'])->toBe($newStartDate->toDateString());

    $page->script(<<<'JS'
        () => {
            window.eventSearchStartDates = [];

            new PerformanceObserver((entries) => {
                window.eventSearchStartDates.push(
                    ...entries.getEntries()
                        .map((entry) => new URL(entry.name).searchParams)
                        .filter((params) => params.has('sw_latitude'))
                        .map((params) => params.get('start_date'))
                );
            }).observe({ type: 'resource', buffered: true });

            return true;
        }
        JS);

    $page->click("button:has-text(\"{$event->user->fullname}\")");
    $page->click('Back to Map');
    $page->assertPresent('#mapContainer');
    $page->wait(6);

    $searchStartDates = $page->script('() => window.eventSearchStartDates');

    expect($searchStartDates)->not->toBeEmpty()
        ->each->toBeEmpty();
});
