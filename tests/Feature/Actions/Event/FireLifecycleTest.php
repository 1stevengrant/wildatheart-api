<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Actions\Event\ExtinguishFire;
use App\Mail\EventAutoUnpublishedMail;
use App\Actions\Event\ConfirmFireIsBurning;
use App\Mail\Fire\ConfirmFireUnpublishedMailable;
use App\Jobs\Fire\UnpublishSingleUnconfirmedFireJob;

beforeEach(function () {
    Bus::fake()->except(UnpublishSingleUnconfirmedFireJob::class);
    Mail::fake();
});

it('confirms a fire awaiting check-in is burning', function () {
    $fire = Event::factory()->fires()->approved()->create([
        'last_checkin_email_sent_at' => now()->subDays(10),
        'last_checkin_reminder_sent_at' => now()->subDays(3),
        'last_checkin_confirmed_at' => null,
    ]);

    ConfirmFireIsBurning::run($fire, $fire->user);

    expect($fire->last_checkin_confirmed_at)->not->toBeNull()
        ->and($fire->last_checkin_email_sent_at->equalTo($fire->last_checkin_confirmed_at))->toBeTrue()
        ->and($fire->last_checkin_reminder_sent_at)->toBeNull()
        ->and($fire->auto_unpublished)->toBeFalse()
        ->and($fire->auto_unpublished_at)->toBeNull()
        ->and($fire->approved_at)->not->toBeNull();

    Bus::assertDispatched(LogActivityJob::class, fn (LogActivityJob $job) => $job->description === 'Fire published'
        && $job->causedBy->is($fire->user)
        && $job->performedOn->is($fire));
});

it('republishes an auto unpublished fire when the host confirms it is burning', function () {
    $fire = Event::factory()->fires()->create([
        'approved_at' => null,
        'auto_unpublished' => true,
        'auto_unpublished_at' => now()->subDay(),
        'last_checkin_email_sent_at' => now()->subDays(14),
        'last_checkin_reminder_sent_at' => now()->subDays(4),
    ]);

    ConfirmFireIsBurning::run($fire, $fire->user);

    expect($fire->approved_at)->not->toBeNull()
        ->and($fire->auto_unpublished)->toBeFalse()
        ->and($fire->auto_unpublished_at)->toBeNull()
        ->and($fire->last_checkin_confirmed_at)->not->toBeNull();
});

it('confirms again after a newer check-in email was sent', function () {
    $fire = Event::factory()->fires()->approved()->create([
        'last_checkin_confirmed_at' => now()->subYear(),
        'last_checkin_email_sent_at' => now()->subDays(5),
    ]);

    ConfirmFireIsBurning::run($fire, $fire->user);

    expect($fire->last_checkin_confirmed_at->isToday())->toBeTrue();

    Bus::assertDispatchedTimes(LogActivityJob::class, 1);
});

it('ignores repeated burning confirmations', function () {
    $fire = Event::factory()->fires()->approved()->create([
        'last_checkin_email_sent_at' => now()->subDays(10),
    ]);

    ConfirmFireIsBurning::run($fire, $fire->user);

    $this->travel(5)->minutes();

    $confirmedAt = $fire->last_checkin_confirmed_at;
    $approvedAt = $fire->approved_at;

    ConfirmFireIsBurning::run($fire, $fire->user);

    expect($fire->last_checkin_confirmed_at->equalTo($confirmedAt))->toBeTrue()
        ->and($fire->approved_at->equalTo($approvedAt))->toBeTrue();

    Bus::assertDispatchedTimes(LogActivityJob::class, 1);
});

it('extinguishes a fire and notifies the owner and managers', function () {
    $fire = Event::factory()->fires()->approved()->create();
    $managers = User::factory()->count(2)->create();
    $fire->managers()->attach($managers);

    ExtinguishFire::run($fire, $fire->user);

    expect($fire->approved_at)->toBeNull()
        ->and($fire->auto_unpublished)->toBeFalse();

    Bus::assertDispatched(LogActivityJob::class, fn (LogActivityJob $job) => $job->description === 'Fire unpublished'
        && $job->causedBy->is($fire->user)
        && $job->performedOn->is($fire));

    Mail::assertQueued(ConfirmFireUnpublishedMailable::class, 3);

    collect([$fire->user])->merge($managers)->each(
        fn (User $recipient) => Mail::assertQueued(
            ConfirmFireUnpublishedMailable::class,
            fn (ConfirmFireUnpublishedMailable $mail) => $mail->hasTo($recipient->email) && $mail->event->is($fire),
        ),
    );
});

it('extinguishes an auto unpublished fire', function () {
    $fire = Event::factory()->fires()->create([
        'approved_at' => null,
        'auto_unpublished' => true,
        'auto_unpublished_at' => now()->subDay(),
    ]);

    ExtinguishFire::run($fire, $fire->user);

    expect($fire->approved_at)->toBeNull()
        ->and($fire->auto_unpublished)->toBeFalse();

    Mail::assertQueued(ConfirmFireUnpublishedMailable::class, 1);
});

it('ignores repeated extinguish requests', function () {
    $fire = Event::factory()->fires()->approved()->create();

    ExtinguishFire::run($fire, $fire->user);
    ExtinguishFire::run($fire, $fire->user);

    Bus::assertDispatchedTimes(LogActivityJob::class, 1);
    Mail::assertQueued(ConfirmFireUnpublishedMailable::class, 1);
});

it('keeps a fire published when the host confirms before a queued auto unpublish runs', function () {
    $fire = Event::factory()->fires()->approved()->create([
        'last_checkin_email_sent_at' => now()->subDays(10),
        'last_checkin_reminder_sent_at' => now()->subDays(4),
        'last_checkin_confirmed_at' => null,
    ]);

    $job = new UnpublishSingleUnconfirmedFireJob(clone $fire);

    ConfirmFireIsBurning::run($fire, $fire->user);

    $job->handle();

    $fire->refresh();

    expect($fire->approved_at)->not->toBeNull()
        ->and($fire->auto_unpublished)->toBeFalse();

    Mail::assertNotQueued(EventAutoUnpublishedMail::class);
});

it('lets the host confirm a fire after it was auto unpublished', function () {
    $fire = Event::factory()->fires()->approved()->create([
        'last_checkin_email_sent_at' => now()->subDays(10),
        'last_checkin_reminder_sent_at' => now()->subDays(4),
        'last_checkin_confirmed_at' => null,
    ]);

    UnpublishSingleUnconfirmedFireJob::dispatch($fire);

    ConfirmFireIsBurning::run($fire, $fire->user);

    expect($fire->approved_at)->not->toBeNull()
        ->and($fire->auto_unpublished)->toBeFalse();

    Mail::assertQueued(EventAutoUnpublishedMail::class);
});

it('does not auto unpublish a fire the host already extinguished', function () {
    $fire = Event::factory()->fires()->approved()->create();

    $job = new UnpublishSingleUnconfirmedFireJob(clone $fire);

    ExtinguishFire::run($fire, $fire->user);

    $job->handle();

    expect($fire->refresh()->auto_unpublished)->toBeFalse();

    Mail::assertNotQueued(EventAutoUnpublishedMail::class);
});
