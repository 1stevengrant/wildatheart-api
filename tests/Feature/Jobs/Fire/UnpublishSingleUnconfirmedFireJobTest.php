<?php

use App\Jobs\Fire\UnpublishSingleUnconfirmedFireJob;
use App\Mail\EventAutoUnpublishedMail;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake()->except(UnpublishSingleUnconfirmedFireJob::class);
    Mail::fake();
});

it('unpublishes a single unconfirmed Fire event', function () {
    $event = Event::factory()->fires()->approved()->create();

    UnpublishSingleUnconfirmedFireJob::dispatch($event);

    $event->refresh();

    expect($event->approved_at)->toBeNull()
        ->and($event->auto_unpublished)->toBeTrue()
        ->and($event->auto_unpublished_at)->not->toBeNull();

    Mail::assertQueued(EventAutoUnpublishedMail::class, function ($mail) use ($event) {
        return $mail->event->is($event);
    });
});
