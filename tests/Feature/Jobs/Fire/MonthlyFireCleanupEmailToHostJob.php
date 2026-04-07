<?php

it('sends the monthly email to a host and updates the timestamps', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $event = \App\Models\Event::factory()->fires()->approved()->create();

    \App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob::dispatch($event);

    $event->refresh();
    expect($event->last_checkin_email_sent_at)->not->toBeNull()
        ->and($event->last_checkin_reminder_sent_at)->toBeNull();

    Mail::assertQueued(\App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable::class, function ($mail) use ($event) {
        return $mail->event->is($event);
    });
});
