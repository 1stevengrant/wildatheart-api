<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Mail\EventAutoUnpublishedMail;
use App\Mail\Fire\SevenDayFollowupMailable;
use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Jobs\Fire\SendFireCheckinsEmailJob;
use App\Jobs\Fire\SendFireCheckinsReminderEmailJob;
use App\Jobs\Fire\AutoUnpublishUnconfirmedFiresJob;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

        // Check that the MonthlyFireCleanupEmailToHostJob was dispatched for this fire
        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class);
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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);

        // Execute the job manually
        (new SendFireCheckinsReminderEmailJob())->handle();

        Mail::assertNotQueued(SevenDayFollowupMailable::class);
    });
});

describe('Auto-unpublishing fires', function () {
    it('unpublishes fires 3 days after reminder with no confirmation', function () {
        $user = User::factory()->create();
        $fire = createFireEvent([
            'user_id' => $user->id,
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch the job
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the job was dispatched
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

        $fire->refresh();
        expect($fire->hasBeenAutoUnpublished())->toBeFalse();

        Mail::assertNotQueued(EventAutoUnpublishedMail::class);
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
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the jobs were dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute the first job manually
        (new SendFireCheckinsEmailJob())->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class);
        $fire->refresh();
        expect($fire->last_checkin_email_sent_at)->not->toBeNull();

        Carbon::setTestNow(now()->addDays(8));

        // Run the command which should dispatch all three jobs
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the jobs were dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class, 2);
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class, 2);
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class, 2);

        // Execute the second job manually
        (new SendFireCheckinsReminderEmailJob())->handle();

        Mail::assertQueued(SevenDayFollowupMailable::class);
        $fire->refresh();
        expect($fire->last_checkin_reminder_sent_at)->not->toBeNull();

        Carbon::setTestNow(now()->addDays(3));

        // Run the command which should dispatch all three jobs
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the jobs were dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class, 3);
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class, 3);
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class, 3);

        // Execute the third job manually
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

        $fire->refresh();
        expect($fire->isApproved())->toBeFalse()
            ->and($fire->hasBeenAutoUnpublished())->toBeTrue();
        Mail::assertQueued(EventAutoUnpublishedMail::class);

        Carbon::setTestNow();
    });

    it(/**
     * @throws Exception
     */ 'handles multiple fires in different states', function () {
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
            'last_checkin_reminder_sent_at' => now()->subDays(4),
            'last_checkin_confirmed_at' => null,
            'auto_unpublished' => false,
        ]);

        // Run the command which should dispatch all three jobs
        $this->artisan('app:send-fires-checkins-email-command');

        // Assert that the jobs were dispatched
        Queue::assertPushed(SendFireCheckinsEmailJob::class);
        Queue::assertPushed(SendFireCheckinsReminderEmailJob::class);
        Queue::assertPushed(AutoUnpublishUnconfirmedFiresJob::class);

        // Execute all jobs manually
        (new SendFireCheckinsEmailJob())->handle();
        (new SendFireCheckinsReminderEmailJob())->handle();
        (new AutoUnpublishUnconfirmedFiresJob())->handle();

        Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, 1);
        Mail::assertQueued(SevenDayFollowupMailable::class, 1);
        Mail::assertQueued(EventAutoUnpublishedMail::class, 1);

        $fireNeedsInitial->refresh();
        expect($fireNeedsInitial->last_checkin_email_sent_at)->not->toBeNull();

        $fireNeedsReminder->refresh();
        expect($fireNeedsReminder->last_checkin_reminder_sent_at)->not->toBeNull();

        $fireToUnpublish->refresh();
        expect($fireToUnpublish->isApproved())->toBeFalse()
            ->and($fireToUnpublish->hasBeenAutoUnpublished())->toBeTrue();
    });
});
