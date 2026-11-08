<?php

use App\Models\Event;
use App\Models\Activity;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\IncreaseEventCountsJob;

beforeEach(function () {
    Bus::fake()->except(IncreaseEventCountsJob::class);
    Mail::fake();

    $this->event = Event::factory()->fires()->approved()->create();
});

it('queues the job to increase event counts', function () {
    Queue::fake();

    $response = $this->get(route('view.event', $this->event));

    expect($response->status())->toBe(200);

    Queue::assertPushed(IncreaseEventCountsJob::class);
});

it('increases the view count for the event', function () {
    $this->get(route('view.event', $this->event));

    expect($this->event->refresh()->view_count)->toBe(1);
});

it('increases a count without logging activity or clearing the event cache', function (string $countType) {
    $activityCountBefore = Activity::query()->count();
    cache()->put('events', 'cached');

    (new IncreaseEventCountsJob($this->event, $countType))->handle();

    expect($this->event->refresh()->{$countType})->toBe(1)
        ->and(Activity::query()->count())->toBe($activityCountBefore)
        ->and(cache()->get('events'))->toBe('cached');
})->with([
    Event::COUNT_TYPE_VIEW,
    Event::COUNT_TYPE_ATTENDEE,
]);

it('still logs event edits', function () {
    $this->event->update(['name' => 'A renamed event']);

    $activity = Activity::query()
        ->where('subject_id', $this->event->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity->properties['attributes']['name'])->toBe('A renamed event');
});
