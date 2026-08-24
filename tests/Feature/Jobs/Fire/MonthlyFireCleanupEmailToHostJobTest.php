<?php

use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Jobs\Fire\SendFireCheckinsReminderEmailJob;
use App\Jobs\Fire\SendSingleFireCheckinReminderEmailJob;
use App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable;

it('sends the monthly email to a host and updates the timestamps', function () {
    Mail::fake();

    $event = Event::factory()->fires()->approved()->create([
        'last_checkin_confirmed_at' => now()->subDays(91),
    ]);

    MonthlyFireCleanupEmailToHostJob::dispatch($event);

    $event->refresh();
    expect($event->last_checkin_email_sent_at)->not->toBeNull()
        ->and($event->last_checkin_reminder_sent_at)->toBeNull()
        ->and($event->last_checkin_confirmed_at)->toBeNull();

    Mail::assertQueued(MonthlyFireCleanupEmailToHostMailable::class, function ($mail) use ($event) {
        return $mail->event->is($event);
    });
});

it('makes a previously confirmed fire eligible for a reminder in the new check-in cycle', function () {
    Mail::fake();

    $event = Event::factory()->fires()->approved()->create([
        'last_checkin_confirmed_at' => now()->subDays(91),
    ]);

    (new MonthlyFireCleanupEmailToHostJob($event))->handle();

    $this->travel(8)->days();
    Queue::fake();

    (new SendFireCheckinsReminderEmailJob)->handle();

    Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) use ($event) {
        return $job->event->is($event);
    });
});
