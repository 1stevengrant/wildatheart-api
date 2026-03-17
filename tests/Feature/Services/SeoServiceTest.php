<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    $this->event = \App\Models\Event::factory()->approved()->fires()->create();
});
it('sets the appropriate meta data for a page', function () {
    $response = $this->get(route('view.event', $this->event));

    expect($response)->assertSeeHtml("Connect with {$this->event->user->fullname} for {$this->event->name} in {$this->event->location}");
});
