<?php

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
it('unpublishes a single unconfirmed Fire event', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $event = \App\Models\Event::factory()->fires()->approved()->create();

    \App\Jobs\Fire\UnpublishSingleUnconfirmedFireJob::dispatch($event);

    $event->refresh();

    expect($event->approved_at)->toBeNull()
        ->and($event->auto_unpublished)->toBeTrue()
        ->and($event->auto_unpublished_at)->not->toBeNull();

    Mail::assertQueued(\App\Mail\EventAutoUnpublishedMail::class, function ($mail) use ($event) {
        return $mail->event->is($event);
    });
});
