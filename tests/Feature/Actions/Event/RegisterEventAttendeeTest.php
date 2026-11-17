<?php

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Data\ConnectWithHostData;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Actions\Event\RegisterEventAttendee;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

beforeEach(function () {
    Queue::fake();

    $this->event = Event::factory()->fires()->create();

    $this->registration = fn (bool $optedIntoMarketing): ConnectWithHostData => new ConnectWithHostData(
        given_name: 'Jane',
        family_name: 'Smith',
        email: 'jane.smith@example.com',
        confirm_email: 'jane.smith@example.com',
        city: 'Denver',
        state: 'Colorado',
        country: 'United States',
        phone: '1234567890',
        opted_into_marketing: $optedIntoMarketing,
    );
});

it('stores the attendee against the event host with a normalized country', function () {
    $attendee = RegisterEventAttendee::run($this->event, ($this->registration)(true));

    expect($attendee->event_id)->toBe($this->event->id)
        ->and($attendee->user_id)->toBe($this->event->user->id)
        ->and($attendee->given_name)->toBe('Jane')
        ->and($attendee->family_name)->toBe('Smith')
        ->and($attendee->email)->toBe('jane.smith@example.com')
        ->and($attendee->phone)->toBe('1234567890')
        ->and($attendee->city)->toBe('Denver')
        ->and($attendee->state)->toBe('Colorado')
        ->and($attendee->country)->toBe('us')
        ->and($attendee->country_code)->toBe('us')
        ->and($attendee->hasOptedIntoMarketing())->toBeTrue();
});

it('dispatches every registration job when the attendee opts into marketing', function () {
    $attendee = RegisterEventAttendee::run($this->event, ($this->registration)(true));

    Queue::assertPushed(LogActivityJob::class, fn (LogActivityJob $job): bool => $job->description === 'Connected with Host'
        && $job->causedBy->is($this->event)
        && $job->performedOn->is($attendee));

    Queue::assertPushed(SubscribeToCommunicationListJob::class, 1);
    Queue::assertPushed(IncreaseEventCountsJob::class, 1);
    Queue::assertPushed(PostAttendeeContactToSlackJob::class, 1);
});

it('skips the marketing subscription when the attendee opts out', function () {
    $attendee = RegisterEventAttendee::run($this->event, ($this->registration)(false));

    expect($attendee->hasOptedIntoMarketing())->toBeFalse();

    Queue::assertNotPushed(SubscribeToCommunicationListJob::class);
    Queue::assertPushed(LogActivityJob::class, 1);
    Queue::assertPushed(IncreaseEventCountsJob::class, 1);
    Queue::assertPushed(PostAttendeeContactToSlackJob::class, 1);
});
