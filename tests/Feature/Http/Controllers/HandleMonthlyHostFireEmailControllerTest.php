<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->fire = Event::factory()->fires()->approved()->create();
});

it('updates a Fire as a result of clicking a link in the email', function () {

    $url = URL::signedRoute('fire.burn', ['event' => $this->fire]);

    $this->get($url);

    $this->fire->refresh();
    expect($this->fire->last_checkin_confirmed_at)->not->toBeNull()
        ->and($this->fire->last_checkin_email_sent_at)->not->toBeNull()
        ->and($this->fire->last_checkin_reminder_sent_at)->toBeNull()
        ->and($this->fire->auto_unpublished)->toBeFalse()
        ->and($this->fire->auto_unpublished_at)->toBeNull()
        ->and($this->fire->approved_at)->not->toBeNull();
});

it('extinguishes a Fire as a result of clicking a link in the email', function () {

    $url = URL::signedRoute('fire.extinguish.destroy', ['event' => $this->fire]);

    $this->post($url);

    $this->fire->refresh();
    expect($this->fire->approved_at)->toBeNull()
        ->and($this->fire->auto_unpublished)->toBeFalse();
});
