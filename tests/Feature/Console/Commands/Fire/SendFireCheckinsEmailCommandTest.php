<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Mail\EventAutoUnpublishedMail;
use App\Jobs\Fire\SendFireCheckinsEmailJob;
use App\Mail\Fire\SevenDayFollowupMailable;
use App\Jobs\Fire\AutoUnpublishUnconfirmedFiresJob;
use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Jobs\Fire\SendFireCheckinsReminderEmailJob;
use App\Jobs\Fire\UnpublishSingleUnconfirmedFireJob;
use App\Jobs\Fire\SendSingleFireCheckinReminderEmailJob;

beforeEach(function () {

    Queue::fake();
    Mail::fake();
});

function createFireEvent(array $attributes = []): Event
{
    $fireEventType = EventType::whereIn('name', [
        EventType::CAPTIVATING_FIRE,
        EventType::FIRES,
    ])->first();

    if (! $fireEventType) {
        throw new Exception('No fire event type found');
    }

    return Event::factory()->create(array_merge([
        'event_type_id' => $fireEventType->id,
        'approved_at' => now(),
    ], $attributes));
}

describe('Initial 90-day check-in emails', function () {
    it('sends check-in emails to fire events that have never received one', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'updated_at' => now()->subDays(91),
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        // Check that the MonthlyFireCleanupEmailToHostJob was dispatched for this fire
        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        // Execute the MonthlyFireCleanupEmailToHostJob manually
        $jobInstance = Queue::pushedJobs()[MonthlyFireCleanupEmailToHostJob::class][0]['job'] ?? null;
        if ($jobInstance) {
            $jobInstance->handle();
        }

        $fire->refresh();
        expect($fire->last_checkin_email_sent_at)->not->toBeNull()
            ->and($fire->last_checkin_reminder_sent_at)->toBeNull();
    });

    it('sends check-in emails to fires with old check-in emails', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(91),
            'updated_at' => now()->subDays(91),
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class);

        // Execute the MonthlyFireCleanupEmailToHostJob manually
        $jobInstance = Queue::pushedJobs()[MonthlyFireCleanupEmailToHostJob::class][0]['job'] ?? null;
        if ($jobInstance) {
            $jobInstance->handle();
        }

        $fire->refresh();
        expect($fire->last_checkin_email_sent_at)->toBeGreaterThan(now()->subMinute());
    });

    it('includes captivating fires in the check-in process', function () {
        $user = User::factory()->create();

        $captivatingFireType = EventType::where('name', EventType::CAPTIVATING_FIRE)->first();
        expect($captivatingFireType)->not->toBeNull('Captivating fire event type should exist');

        Event::factory()
            ->create([
                'event_type_id' => $captivatingFireType->id,
                'approved_at' => now(),
                'user_id' => $user->id,
                'last_checkin_email_sent_at' => null,
                'updated_at' => now()->subDays(91),
                'last_checkin_confirmed_at' => null,
            ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class);
    });

    it('does not send emails to unapproved fires', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'approved_at' => null,
            'last_checkin_email_sent_at' => null,
            'updated_at' => now()->subDays(91),
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class);
    });

    it('does not send emails to non-fire events', function () {
        $user = User::factory()->create();
        Event::factory()
            ->wahExperiences()
            ->approved()
            ->create([
                'user_id' => $user->id,
                'last_checkin_email_sent_at' => null,
                'updated_at' => now()->subDays(91),
            ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class);
    });

    it('does not send emails to recently updated fires', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'updated_at' => now()->subDays(89),
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class);
    });

    it('does not send emails to fires with recent check-in confirmations', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'updated_at' => now()->subDays(91),
            'last_checkin_confirmed_at' => now()->subDays(30),
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class);
    });

    it('resets reminder sent timestamp when sending check-in email', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'last_checkin_reminder_sent_at' => now()->subDays(50),
            'updated_at' => now()->subDays(91),
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        (new MonthlyFireCleanupEmailToHostJob($fire))->handle();

        $fire->refresh();
        expect($fire->last_checkin_reminder_sent_at)->toBeNull();
    });

    it('handles fires with null updated_at dates', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'updated_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class);
    });
});

describe('7-day follow-up reminder emails', function () {
    it('sends reminder emails to fires that received check-in email 7+ days ago', function () {
        $user = User::factory()->create();

        expect($user->email)->not->toBeNull('User should have an email');

        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(8),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that the individual job was dispatched
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        // Execute the individual job manually
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) {
            $job->handle();

            return true;
        });

        Mail::assertQueued(SevenDayFollowupMailable::class, function ($mail) use ($fire) {
            return $mail->event->id === $fire->id;
        });

        $fire->refresh();
        expect($fire->last_checkin_reminder_sent_at)->not->toBeNull();
    });

    it('does not send reminder to fires that already received one', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(8),
            'last_checkin_reminder_sent_at' => now()->subDays(1),
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that no individual jobs were dispatched
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class);

        Mail::assertNotQueued(SevenDayFollowupMailable::class);
    });

    it('does not send reminder to fires with recent check-in emails', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(6),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that no individual jobs were dispatched
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class);

        Mail::assertNotQueued(SevenDayFollowupMailable::class);
    });

    it('does not send reminder to fires that confirmed check-in', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(8),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => now()->subDays(1),
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that no individual jobs were dispatched
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class);

        Mail::assertNotQueued(SevenDayFollowupMailable::class);
    });

    it('does not send reminder to unapproved fires', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'approved_at' => null,
            'last_checkin_email_sent_at' => now()->subDays(8),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that no individual jobs were dispatched
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class);

        Mail::assertNotQueued(SevenDayFollowupMailable::class);
    });
});

describe('Auto-unpublishing fires', function () {
    it('unpublishes fires 3 days after reminder with no confirmation', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(14), // Initial email 14 days ago
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        // Assert that the individual job was dispatched
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        // Execute the individual job manually
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) {
            $job->handle();

            return true;
        });

        $fire->refresh();
        expect($fire->isApproved())->toBeFalse()
            ->and($fire->hasBeenAutoUnpublished())->toBeTrue();

        Mail::assertQueued(EventAutoUnpublishedMail::class, function ($mail) use ($fire) {
            return $mail->event->id === $fire->id;
        });
    });

    it('does not unpublish fires that confirmed check-in', function () {
        $user = User::factory()->create();
        $originalApprovedAt = now()->subDays(100);

        $fireEventType = EventType::whereIn('name', [
            EventType::CAPTIVATING_FIRE,
            EventType::FIRES,
        ])->first();

        $fire = Event::factory()
            ->create([
                'event_type_id' => $fireEventType->id,
                'user_id' => $user->id,
                'approved_at' => $originalApprovedAt,
                'last_checkin_reminder_sent_at' => now()->subDays(4),
                'last_checkin_confirmed_at' => now()->subDay(),
                'auto_unpublished' => false,
            ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        $fire->refresh();
        expect($fire->isApproved())->toBeTrue()
            ->and($fire->hasBeenAutoUnpublished())->toBeFalse();

        Mail::assertNotSent(EventAutoUnpublishedMail::class);
    });

    it('does not unpublish fires with recent reminders', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_reminder_sent_at' => now()->subDays(2),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        $fire->refresh();
        expect($fire->isApproved())->toBeTrue()
            ->and($fire->hasBeenAutoUnpublished())->toBeFalse();
    });

    it('does not unpublish fires without reminder emails', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        $fire->refresh();
        expect($fire->isApproved())->toBeTrue()
            ->and($fire->hasBeenAutoUnpublished())->toBeFalse();
    });

    it('does not unpublish already auto-unpublished fires', function () {
        $user = User::factory()->create();
        createFireEvent([
            'user_id' => $user->id,
            'approved_at' => null,
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => true,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        Mail::assertNotQueued(EventAutoUnpublishedMail::class);
    });

    it('does not unpublish unapproved fires', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'approved_at' => null,
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('fire:send-checkins');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        $fire->refresh();
        expect($fire->hasBeenAutoUnpublished())->toBeFalse();

        Mail::assertNotQueued(EventAutoUnpublishedMail::class);
    });
});

describe('Mutual exclusivity', function () {
    it('does not send reminder to fire that qualifies for initial email', function () {
        // A fire with initial email 100 days ago qualifies for a NEW initial email
        // It should NOT receive a reminder email (even though 100 days > 7 days)
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(100), // Qualifies for initial email
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
            'updated_at' => now()->subDays(100),
        ]);

        $this->artisan('fire:send-checkins');

        // Execute the reminder job
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Should NOT dispatch reminder for this fire - it needs initial email first
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        // But initial email job SHOULD pick it up
        (new SendFireCheckinsEmailJob)->handle();
        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });
    });

    it('does not unpublish fire that qualifies for initial email', function () {
        // A fire that qualifies for initial email should NOT be unpublished
        // even if it has an old reminder timestamp
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(100), // Qualifies for initial email
            'last_checkin_reminder_sent_at' => now()->subDays(95), // Old reminder
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
            'updated_at' => now()->subDays(100),
        ]);

        $this->artisan('fire:send-checkins');

        // Execute the unpublish job
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        // Should NOT dispatch unpublish for this fire - it needs initial email first
        Queue::assertNotPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        $fire->refresh();
        expect($fire->isApproved())->toBeTrue()
            ->and($fire->hasBeenAutoUnpublished())->toBeFalse();
    });

    it('processes fire through correct workflow without overlap', function () {
        $user = User::factory()->create();

        // Fire A: needs initial email (100 days old)
        $fireA = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(100),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
            'updated_at' => now()->subDays(100),
        ]);

        // Fire B: needs reminder (10 days since initial, no reminder yet)
        $fireB = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(10),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        // Fire C: needs unpublish (14 days since initial, 4 days since reminder)
        $fireC = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(14),
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        $this->artisan('fire:send-checkins');

        // Execute all parent jobs
        (new SendFireCheckinsEmailJob)->handle();
        (new SendFireCheckinsReminderEmailJob)->handle();
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        // Fire A should ONLY get initial email (not reminder, not unpublish)
        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, fn ($job) => $job->event->id === $fireA->id);
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class, fn ($job) => $job->event->id === $fireA->id);
        Queue::assertNotPushed(UnpublishSingleUnconfirmedFireJob::class, fn ($job) => $job->event->id === $fireA->id);

        // Fire B should ONLY get reminder (not initial, not unpublish)
        Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class, fn ($job) => $job->event->id === $fireB->id);
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, fn ($job) => $job->event->id === $fireB->id);
        Queue::assertNotPushed(UnpublishSingleUnconfirmedFireJob::class, fn ($job) => $job->event->id === $fireB->id);

        // Fire C should ONLY get unpublished (not initial, not reminder)
        Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class, fn ($job) => $job->event->id === $fireC->id);
        Queue::assertNotPushed(SendSingleFireCheckinReminderEmailJob::class, fn ($job) => $job->event->id === $fireC->id);
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, fn ($job) => $job->event->id === $fireC->id);
    });
});

describe('Full workflow integration', function () {
    it('processes a fire through the complete workflow', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'updated_at' => now()->subDays(91),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        Carbon::setTestNow(now());

        // Run the command which should dispatch all three jobs
        $this->artisan('fire:send-checkins');

        // Assert that the jobs were dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the first job manually
        (new SendFireCheckinsEmailJob)->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class);
        (new MonthlyFireCleanupEmailToHostJob($fire))->handle();

        $fire->refresh();
        expect($fire->last_checkin_email_sent_at)->not->toBeNull();

        Carbon::setTestNow(now()->addDays(8));

        // Run the command - jobs implement ShouldBeUnique so won't be re-dispatched
        // while previous instances are still in the queue (Queue::fake doesn't complete them)
        $this->artisan('fire:send-checkins');

        // Execute the reminder job manually
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that the individual job was dispatched
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        // Execute the individual job manually
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) {
            $job->handle();

            return true;
        });

        Mail::assertQueued(SevenDayFollowupMailable::class);
        $fire->refresh();
        expect($fire->last_checkin_reminder_sent_at)->not->toBeNull();

        Carbon::setTestNow(now()->addDays(3));

        // Run the command - jobs implement ShouldBeUnique so won't be re-dispatched
        $this->artisan('fire:send-checkins');

        // Execute the unpublish job manually
        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        // Assert that the individual job was dispatched
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        // Execute the individual job manually
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) {
            $job->handle();

            return true;
        });

        $fire->refresh();
        expect($fire->isApproved())->toBeFalse()
            ->and($fire->hasBeenAutoUnpublished())->toBeTrue();
        Mail::assertQueued(EventAutoUnpublishedMail::class);

        Carbon::setTestNow();
    });

    it('handles multiple fires in different states', function () {
        $user = User::factory()->create();

        $fireNeedsInitial = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => null,
            'updated_at' => now()->subDays(91),
            'last_checkin_confirmed_at' => null,
        ]);

        $fireNeedsReminder = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(8),
            'last_checkin_reminder_sent_at' => null,
            'last_checkin_confirmed_at' => null,
        ]);

        $fireToUnpublish = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_email_sent_at' => now()->subDays(14), // Initial email 14 days ago
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch all three jobs
        $this->artisan('fire:send-checkins');

        // Assert that the jobs were dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute all jobs manually
        (new SendFireCheckinsEmailJob)->handle();
        (new SendFireCheckinsReminderEmailJob)->handle();

        // Assert that the individual job was dispatched for the fire that needs a reminder
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) use ($fireNeedsReminder) {
            return $job->event->id === $fireNeedsReminder->id;
        });

        // Execute the individual job manually
        Queue::assertPushed(SendSingleFireCheckinReminderEmailJob::class, function ($job) {
            $job->handle();

            return true;
        });

        (new AutoUnpublishUnconfirmedFiresJob)->handle();

        // Assert that the individual job was dispatched for the fire to unpublish
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) use ($fireToUnpublish) {
            return $job->event->id === $fireToUnpublish->id;
        });

        // Execute the individual job manually
        Queue::assertPushed(UnpublishSingleUnconfirmedFireJob::class, function ($job) {
            $job->handle();

            return true;
        });

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, 1);
        Mail::assertQueued(SevenDayFollowupMailable::class, 1);
        Mail::assertQueued(EventAutoUnpublishedMail::class, 1);

        (new MonthlyFireCleanupEmailToHostJob($fireNeedsInitial))->handle();

        $fireNeedsInitial->refresh();
        expect($fireNeedsInitial->last_checkin_email_sent_at)->not->toBeNull();

        $fireNeedsReminder->refresh();
        expect($fireNeedsReminder->last_checkin_reminder_sent_at)->not->toBeNull();

        $fireToUnpublish->refresh();
        expect($fireToUnpublish->isApproved())->toBeFalse()
            ->and($fireToUnpublish->hasBeenAutoUnpublished())->toBeTrue();
    });
});
