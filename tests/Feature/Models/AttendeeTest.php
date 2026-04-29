<?php

use App\Models\Event;
use App\Models\Attendee;
use App\Data\AttendeeData;
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

    expect($attendee->given_name)->toBeNull()
        ->and($attendee->family_name)->toBeNull()
        ->and($attendee->email)->toBeNull()
        ->and($attendee->city)->toBeNull();
});

it('builds AttendeeData from a model with null string fields', function () {
    $event = Event::factory()->fires()->approved()->create();

    $attendee = Attendee::factory()->for($event)->create([
        'given_name' => null,
        'family_name' => null,
        'email' => null,
        'country' => null,
        'country_code' => null,
        'phone' => null,
        'city' => null,
        'state' => null,
    ]);

    $data = AttendeeData::fromModel($attendee->fresh(['event.eventType']));

    expect($data->given_name)->toBeNull()
        ->and($data->family_name)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->country)->toBeNull()
        ->and($data->country_code)->toBeNull()
        ->and($data->phone)->toBeNull()
        ->and($data->city)->toBeNull()
        ->and($data->state)->toBeNull();
});

it('formats given name, family name, email, and city when present', function () {
    $event = Event::factory()->fires()->approved()->create();

    $attendee = Attendee::factory()->for($event)->create([
        'given_name' => 'jamie',
        'family_name' => 'morrison',
        'email' => 'JAMIE@EXAMPLE.COM',
        'city' => 'colorado springs',
    ]);

    expect($attendee->given_name)->toBe('Jamie')
        ->and($attendee->family_name)->toBe('Morrison')
        ->and($attendee->email)->toBe('jamie@example.com')
        ->and($attendee->city)->toBe('Colorado Springs');
});
