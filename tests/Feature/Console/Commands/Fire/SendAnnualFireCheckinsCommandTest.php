<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Fire\AnnualFireCheckinEmailJob;

beforeEach(function () {

    Queue::fake();
});

function createFireEventForAnnual(array $attributes = []): Event
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
        'created_at' => now()->subDays(60),
    ], $attributes));
}

describe('Annual check-in eligibility', function () {
    it('sends check-in to fire not confirmed in 30+ days', function () {
        $user = User::factory()->create();
        $fire = createFireEventForAnnual([
            'user_id' => $user->id,
            'last_checkin_confirmed_at' => now()->subDays(31),
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertPushed(AnnualFireCheckinEmailJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });
    });

    it('sends check-in to fire never confirmed', function () {
        $user = User::factory()->create();
        $fire = createFireEventForAnnual([
            'user_id' => $user->id,
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertPushed(AnnualFireCheckinEmailJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });
    });

    it('skips fires confirmed within 30 days', function () {
        $user = User::factory()->create();
        createFireEventForAnnual([
            'user_id' => $user->id,
            'last_checkin_confirmed_at' => now()->subDays(29),
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertNotPushed(AnnualFireCheckinEmailJob::class);
    });

    it('skips fires created within 30 days (grace period)', function () {
        $user = User::factory()->create();

        $fireEventType = EventType::whereIn('name', [
            EventType::CAPTIVATING_FIRE,
            EventType::FIRES,
        ])->first();

        Event::factory()->create([
            'event_type_id' => $fireEventType->id,
            'approved_at' => now(),
            'user_id' => $user->id,
            'created_at' => now()->subDays(20),
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertNotPushed(AnnualFireCheckinEmailJob::class);
    });

    it('skips fires already in active check-in cycle', function () {
        $user = User::factory()->create();
        createFireEventForAnnual([
            'user_id' => $user->id,
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => now()->subDays(10),
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertNotPushed(AnnualFireCheckinEmailJob::class);
    });

    it('includes both Fire and Captivating Fire event types', function () {
        $user = User::factory()->create();

        $fireType = EventType::where('name', EventType::FIRES)->first();
        $captivatingType = EventType::where('name', EventType::CAPTIVATING_FIRE)->first();

        $fire = Event::factory()->create([
            'event_type_id' => $fireType->id,
            'approved_at' => now(),
            'user_id' => $user->id,
            'created_at' => now()->subDays(60),
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $captivatingFire = Event::factory()->create([
            'event_type_id' => $captivatingType->id,
            'approved_at' => now(),
            'user_id' => $user->id,
            'created_at' => now()->subDays(60),
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertPushed(AnnualFireCheckinEmailJob::class, 2);
        Queue::assertPushed(AnnualFireCheckinEmailJob::class, fn ($job) => $job->event->id === $fire->id);
        Queue::assertPushed(AnnualFireCheckinEmailJob::class, fn ($job) => $job->event->id === $captivatingFire->id);
    });

    it('skips unpublished fires', function () {
        $user = User::factory()->create();
        createFireEventForAnnual([
            'user_id' => $user->id,
            'approved_at' => null,
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertNotPushed(AnnualFireCheckinEmailJob::class);
    });

    it('skips non-fire event types', function () {
        $user = User::factory()->create();

        Event::factory()
            ->wahExperiences()
            ->approved()
            ->create([
                'user_id' => $user->id,
                'created_at' => now()->subDays(60),
                'last_checkin_confirmed_at' => null,
                'last_checkin_email_sent_at' => null,
            ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertNotPushed(AnnualFireCheckinEmailJob::class);
    });
});

describe('Command options', function () {
    it('dry-run mode shows eligible fires without sending', function () {
        $user = User::factory()->create();
        $fire = createFireEventForAnnual([
            'user_id' => $user->id,
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain($fire->name);

        Queue::assertNotPushed(AnnualFireCheckinEmailJob::class);
    });

    it('limit option restricts batch size', function () {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            createFireEventForAnnual([
                'user_id' => $user->id,
                'last_checkin_confirmed_at' => null,
                'last_checkin_email_sent_at' => null,
            ]);
        }

        $this->artisan('fire:annual-checkin', ['--limit' => 2])
            ->assertSuccessful();

        Queue::assertPushed(AnnualFireCheckinEmailJob::class, 2);
    });
});

describe('Integration with existing flow', function () {
    it('sends email that will be picked up by daily reminder job', function () {
        $user = User::factory()->create();
        $fire = createFireEventForAnnual([
            'user_id' => $user->id,
            'last_checkin_confirmed_at' => null,
            'last_checkin_email_sent_at' => null,
        ]);

        $this->artisan('fire:annual-checkin')
            ->assertSuccessful();

        Queue::assertPushed(AnnualFireCheckinEmailJob::class, function ($job) use ($fire) {
            return $job->event->id === $fire->id;
        });

        $jobInstance = Queue::pushedJobs()[AnnualFireCheckinEmailJob::class][0]['job'] ?? null;
        if ($jobInstance) {
            $jobInstance->handle();
        }

        $fire->refresh();
        expect($fire->last_checkin_email_sent_at)->not->toBeNull()
            ->and($fire->last_checkin_reminder_sent_at)->toBeNull();
    });
});
