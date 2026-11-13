<?php

use App\Models\Event;

it('shows the name the host types in the Fire preview', function () {
    // Browser hits wahfires.test, which is served against the local database.
    $host = Event::on('local')->where('event_type_id', 1)->latest()->value('user_id');

    if (! $host) {
        $this->markTestSkipped('No fire host found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$host}&redirect=/host/fire");

    $page->assertPresent('#name');
    $page->fill('#name', 'Riverside');

    $page->assertSee('Riverside Fire');
});

it('shows the event name on the connect page without adding a Fire suffix', function () {
    $fire = Event::on('local')->published()->active()->where('event_type_id', 1)->whereNotNull('name')->first();
    $retreat = Event::on('local')->published()->active()->where('event_type_id', '!=', 1)->first();

    if (! $fire || ! $retreat) {
        $this->markTestSkipped('No published fire and scheduled event found in local database');
    }

    $this->visit("https://wahfires.test/connect/{$fire->id}")
        ->assertSee("{$fire->name} Fire")
        ->assertDontSee("{$fire->name} Fire Fire");

    $this->visit("https://wahfires.test/connect/{$retreat->id}")
        ->assertDontSee("{$retreat->eventType->name} Fire");
});
