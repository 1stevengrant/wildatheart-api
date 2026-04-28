<?php

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('can be created with null name, email, and city without throwing', function () {
    $event = Event::factory()->fires()->approved()->create();

    $attendee = Attendee::factory()->for($event)->create([
        'given_name' => null,
        'family_name' => null,
        'email' => null,
        'city' => null,
    ]);

    expect($attendee->given_name)->toBeNull();
    expect($attendee->family_name)->toBeNull();
    expect($attendee->email)->toBeNull();
    expect($attendee->city)->toBeNull();
});

it('formats given name, family name, email, and city when present', function () {
    $event = Event::factory()->fires()->approved()->create();

    $attendee = Attendee::factory()->for($event)->create([
        'given_name' => 'jamie',
        'family_name' => 'morrison',
        'email' => 'JAMIE@EXAMPLE.COM',
        'city' => 'colorado springs',
    ]);

    expect($attendee->given_name)->toBe('Jamie');
    expect($attendee->family_name)->toBe('Morrison');
    expect($attendee->email)->toBe('jamie@example.com');
    expect($attendee->city)->toBe('Colorado Springs');
});
