<?php

use App\Models\Event;
use App\Models\EventType;

/*
 * Browser hits wahfires.test, which is served against the local database.
 * Every submit is captured and aborted so nothing is written.
 */
const CAPTURE_SUBMITTED_BODY = <<<'JS'
    () => {
        window.submittedBody = null;

        const send = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.send = function (body) {
            XMLHttpRequest.prototype.send = send;
            window.submittedBody = body;
            this.abort();
        };

        return true;
    }
    JS;

it('submits the address fields on the mailing address page', function () {
    $hostId = Event::on('local')->latest()->value('user_id');
    $eventType = EventType::on('local')->first();

    if (! $hostId || ! $eventType) {
        $this->markTestSkipped('No host or event type found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$hostId}&redirect=/host/{$eventType->slug}/mailing");

    $page->assertSee('Mailing Address')
        ->fill('#given_name', 'Ada')
        ->fill('#family_name', 'Lovelace')
        ->fill('#address', '1 Main Street')
        ->fill('#address2', 'Suite 2')
        ->select('#country', 'United States')
        ->select('#state', 'Texas')
        ->fill('#city', 'Austin')
        ->fill('#zip', '73301')
        ->fill('.react-international-phone-input', '+1 512 555 0100')
        ->script(CAPTURE_SUBMITTED_BODY);

    $page->press('Save Mailing Address');
    $page->wait(1);

    $body = json_decode($page->script('() => window.submittedBody'), true);

    expect($body)->toMatchArray([
        'given_name' => 'Ada',
        'family_name' => 'Lovelace',
        'street_address' => '1 Main Street',
        'address2' => 'Suite 2',
        'country' => 'United States',
        'state' => 'Texas',
        'city' => 'Austin',
        'zip' => '73301',
    ]);
});

it('submits the address fields on the gift page', function () {
    $event = Event::on('local')->where('gift_requested', false)->latest()->first();

    if (! $event) {
        $this->markTestSkipped('No event without a gift request found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/gift/{$event->id}");

    $page->assertSee('Firestarter kit')
        ->fill('#given_name', 'Ada')
        ->fill('#address', '1 Main Street')
        ->select('#country', 'United States')
        ->select('#state', 'Texas')
        ->fill('#city', 'Austin')
        ->fill('#zip', '73301')
        ->fill('.react-international-phone-input', '+1 512 555 0100')
        ->script(CAPTURE_SUBMITTED_BODY);

    $page->press('Send Me My Gift');
    $page->wait(1);

    $body = json_decode($page->script('() => window.submittedBody'), true);

    expect($body)->toMatchArray([
        'given_name' => 'Ada',
        'street_address' => '1 Main Street',
        'state' => 'Texas',
        'city' => 'Austin',
        'zip' => '73301',
    ]);
});

it('submits the text fields on the connect page', function () {
    $event = Event::on('local')->published()->active()->first();

    if (! $event) {
        $this->markTestSkipped('No published event found in local database');
    }

    $page = $this->visit("https://wahfires.test/connect/{$event->id}");

    $page->fill('#given_name', 'Ada')
        ->fill('#family_name', 'Lovelace')
        ->fill('#email', 'ada@example.com')
        ->fill('#confirm_email', 'ada@example.com')
        ->fill('#city', 'Austin')
        ->script(CAPTURE_SUBMITTED_BODY);

    $page->click('button:has-text("Connect with")');
    $page->wait(1);

    $body = json_decode($page->script('() => window.submittedBody'), true);

    expect($body)->toMatchArray([
        'given_name' => 'Ada',
        'family_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'confirm_email' => 'ada@example.com',
        'city' => 'Austin',
    ]);
});

it('opens and cancels the event admin modals', function () {
    $event = Event::on('local')->published()->active()->latest()->first();

    if (! $event) {
        $this->markTestSkipped('No published event found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$event->user_id}&redirect=/events/active");

    $page->click('button:has-text("Options") >> nth=0')
        ->click('button:has-text("Unpublish Event") >> nth=0')
        ->assertSee('Are you sure you want to unpublish this Event')
        ->press('Cancel')
        ->assertDontSee('Are you sure you want to unpublish this Event');

    $page->click('button:has-text("Options") >> nth=0')
        ->click('button:has-text("Delete Event") >> nth=0')
        ->assertSee('Are you sure you want to delete this event?')
        ->click('[aria-label="Close modal"]')
        ->assertDontSee('Are you sure you want to delete this event?');
});
