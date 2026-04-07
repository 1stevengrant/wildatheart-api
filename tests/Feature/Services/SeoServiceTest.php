<?php

use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    $this->event = Event::factory()->approved()->fires()->create();
});
it('sets the appropriate meta data for a page', function () {
    $response = $this->get(route('view.event', $this->event));

    $expectedTitle = "Join {$this->event->host_name}'s " . Str::singular($this->event->eventType->name);
    expect($response)->assertSeeHtml($expectedTitle);
});
