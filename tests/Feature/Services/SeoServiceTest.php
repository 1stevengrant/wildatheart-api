<?php

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->event = \App\Models\Event::factory()->approved()->fires()->create();
});
it('sets the appropriate meta data for a page', function () {
    $response = $this->get(route('view.event', $this->event));

    expect($response)->assertSeeHtml("Connect with {$this->event->host_name} for {$this->event->name} in {$this->event->location}");
});
