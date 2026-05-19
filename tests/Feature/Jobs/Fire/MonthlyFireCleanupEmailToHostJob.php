<?php

use App\Models\Event;
use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable;

it('sends the monthly email to a host and updates the timestamps', function () {
    Illuminate\Support\Facades\Mail::fake();

    $event = Event::factory()->fires()->approved()->create();

    MonthlyFireCleanupEmailToHostJob::dispatch($event);

    $event->refresh();
    expect($event->last_checkin_email_sent_at)->not->toBeNull()
        ->and($event->last_checkin_reminder_sent_at)->toBeNull();

    Mail::assertQueued(MonthlyFireCleanupEmailToHostMailable::class, function ($mail) use ($event) {
        return $mail->event->is($event);
    });
});
