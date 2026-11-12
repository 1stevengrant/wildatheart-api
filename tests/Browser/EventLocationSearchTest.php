<?php

use App\Models\Event;

it('fills the location from a searched place without carrying it into the next event the host edits', function () {
    // Browser hits wahfires.test, which is served against the local database.
    $activeFires = fn () => Event::on('local')
        ->published()
        ->active()
        ->where('event_type_id', 1);

    $hostId = $activeFires()
        ->groupBy('user_id')
        ->havingRaw('count(*) >= 2')
        ->value('user_id');

    if (! $hostId) {
        $this->markTestSkipped('No host with two active fires found in local database');
    }

    [$searchedEvent, $otherEvent] = $activeFires()
        ->where('user_id', $hostId)
        ->limit(2)
        ->get(['id', 'latitude', 'longitude'])
        ->all();

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

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$hostId}&redirect=/events/{$searchedEvent->id}");

    $page->type('#event_location', 'Wellington, New Zealand');
    $page->assertPresent('[role="option"]');
    $page->script('() => document.querySelector(\'[role="option"]\').click()');
    $page->wait(2);

    $page->script($blockNextSave);
    $page->press('Update This Fire');
    $page->wait(1);
    $searchedEventBody = json_decode($page->script('() => window.submittedEventBody'), true);

    expect((float) $searchedEventBody['latitude'])->toEqualWithDelta(-41.29, 0.1)
        ->and((float) $searchedEventBody['longitude'])->toEqualWithDelta(174.78, 0.1);

    $otherEventEditLink = "a[href$=\"/events/{$otherEvent->id}\"]";

    $page->click('Cancel');
    $page->assertPresent($otherEventEditLink);
    $page->script("() => document.querySelector('{$otherEventEditLink}').closest('.relative').querySelector('button').click()");
    $page->click($otherEventEditLink);
    $page->assertPresent('#event_location');
    $page->wait(2);

    $page->script($blockNextSave);
    $page->press('Update This Fire');
    $page->wait(1);
    $otherEventBody = json_decode($page->script('() => window.submittedEventBody'), true);

    expect((float) $otherEventBody['latitude'])->toEqualWithDelta((float) $otherEvent->latitude, 0.0001)
        ->and((float) $otherEventBody['longitude'])->toEqualWithDelta((float) $otherEvent->longitude, 0.0001);
});
