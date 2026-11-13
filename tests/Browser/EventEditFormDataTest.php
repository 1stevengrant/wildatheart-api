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

it('sends only the scheduled event fields and the toggled privacy', function () use ($blockNextSave) {
    // Browser hits wahfires.test, which is served against the local database.
    $event = Event::on('local')
        ->published()
        ->active()
        ->where('event_type_id', '!=', 1)
        ->where('start_date', '>', now())
        ->first();

    if (! $event) {
        $this->markTestSkipped('No upcoming scheduled event found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/{$event->id}");

    $page->assertPresent('#event_location');
    $page->click('[role="switch"]');

    $page->script($blockNextSave);
    $page->click('button:has-text("Update This")');
    $page->wait(1);

    $submitted = json_decode($page->script('() => window.submittedEventBody'), true);

    expect($submitted)
        ->not->toHaveKeys(['name', 'frequency'])
        ->and($submitted['is_private'])->toBe(! $event->is_private)
        ->and((float) $submitted['latitude'])->toEqualWithDelta((float) $event->latitude, 0.0001)
        ->and($submitted['start_date'])->toBe($event->start_date->toDateString());
});

it('sends the fire name and frequency from the fire edit form', function () use ($blockNextSave) {
    $event = Event::on('local')->published()->active()->where('event_type_id', 1)->whereNotNull('frequency')->first();

    if (! $event) {
        $this->markTestSkipped('No active fire found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/{$event->id}");

    $page->assertPresent('#event_location');

    $page->script($blockNextSave);
    $page->click('button:has-text("Update This")');
    $page->wait(1);

    $submitted = json_decode($page->script('() => window.submittedEventBody'), true);

    expect($submitted['name'])->toBe($event->name)
        ->and($submitted['frequency'])->toBe($event->frequency->value)
        ->and((float) $submitted['longitude'])->toEqualWithDelta((float) $event->longitude, 0.0001);
});
