<?php

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\SendRetreatThankYouEmailJob;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
});

function createEndedEvent(string $eventTypeName, array $attributes = []): Event
{
    return Event::factory()->approved()->createQuietly(array_merge([
        'event_type_id' => EventType::where('name', $eventTypeName)->first()->id,
        'start_date' => now()->subDays(4),
        'end_date' => now()->subDay(),
    ], $attributes));
}

it('queues a thank-you email for each retreat type that ended yesterday', function (string $eventTypeName) {
    $event = createEndedEvent($eventTypeName);

    $this->artisan('event:send-retreat-thank-you')->assertSuccessful();

    Queue::assertPushed(SendRetreatThankYouEmailJob::class, fn ($job) => $job->event->is($event));
})->with([
    EventType::WAH_RETREAT,
    EventType::CAPTIVATING_CORE_RETREAT,
    EventType::CAPTIVATING_CORE_RETREAT_1_DAY,
    EventType::BECOMING_A_KING,
    EventType::SURVIVAL_SCHOOL,
]);

it('catches up on retreats that ended within the last week', function () {
    createEndedEvent(EventType::WAH_RETREAT, ['end_date' => now()->subDays(6)]);

    $this->artisan('event:send-retreat-thank-you')->assertSuccessful();

    Queue::assertPushed(SendRetreatThankYouEmailJob::class, 1);
});

it('skips events that are not eligible', function (array $attributes, string $eventTypeName) {
    createEndedEvent($eventTypeName, $attributes);

    $this->artisan('event:send-retreat-thank-you')->assertSuccessful();

    Queue::assertNotPushed(SendRetreatThankYouEmailJob::class);
})->with([
    'unpublished' => [['approved_at' => null], EventType::WAH_RETREAT],
    'already sent' => [['post_event_email_sent_at' => now()->subHour()], EventType::WAH_RETREAT],
    'still running' => [['end_date' => now()->addHour()], EventType::WAH_RETREAT],
    'ended over a week ago' => [['end_date' => now()->subDays(8)], EventType::WAH_RETREAT],
    'fire' => [[], EventType::FIRES],
    'experience' => [[], EventType::WAH_EXPERIENCE],
]);

it('skips soft deleted retreats', function () {
    createEndedEvent(EventType::WAH_RETREAT)->delete();

    $this->artisan('event:send-retreat-thank-you')->assertSuccessful();

    Queue::assertNotPushed(SendRetreatThankYouEmailJob::class);
});

it('does not queue anything on a dry run', function () {
    createEndedEvent(EventType::WAH_RETREAT);

    $this->artisan('event:send-retreat-thank-you --dry-run')
        ->expectsOutputToContain('Found 1 eligible retreat(s).')
        ->assertSuccessful();

    Queue::assertNotPushed(SendRetreatThankYouEmailJob::class);
});
