<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\RetreatThankYouMailable;
use App\Jobs\Event\SendRetreatThankYouEmailJob;

beforeEach(function () {
    Mail::fake();
});

function endedWahRetreat(): Event
{
    return Event::factory()->wahRetreat()->approved()->createQuietly([
        'start_date' => now()->subDays(4),
        'end_date' => now()->subDay(),
    ]);
}

it('emails the host and records when it was sent', function () {
    $event = endedWahRetreat();

    SendRetreatThankYouEmailJob::dispatchSync($event);

    Mail::assertQueued(RetreatThankYouMailable::class, function (RetreatThankYouMailable $mail) use ($event) {
        return $mail->hasTo($event->user->email)
            && $mail->emailTemplate->slug === 'wah-retreat-thank-you';
    });

    expect($event->fresh()->post_event_email_sent_at)->not->toBeNull();
});

it('ccs co-hosts when the template has cc co-hosts enabled', function () {
    $event = endedWahRetreat();
    $coHost = User::factory()->create();
    $event->managers()->attach($coHost);

    SendRetreatThankYouEmailJob::dispatchSync($event->fresh());

    Mail::assertQueued(RetreatThankYouMailable::class, fn (RetreatThankYouMailable $mail) => $mail->hasCc($coHost->email));
});

it('does not cc co-hosts when the template has cc co-hosts disabled', function () {
    EmailTemplate::where('slug', 'wah-retreat-thank-you')->update(['cc_co_hosts' => false]);

    $event = endedWahRetreat();
    $coHost = User::factory()->create();
    $event->managers()->attach($coHost);

    SendRetreatThankYouEmailJob::dispatchSync($event->fresh());

    Mail::assertQueued(RetreatThankYouMailable::class, fn (RetreatThankYouMailable $mail) => ! $mail->hasCc($coHost->email));
});

it('does not send when the template is inactive', function () {
    EmailTemplate::where('slug', 'wah-retreat-thank-you')->update(['is_active' => false]);

    $event = endedWahRetreat();

    SendRetreatThankYouEmailJob::dispatchSync($event);

    Mail::assertNotQueued(RetreatThankYouMailable::class);
    expect($event->fresh()->post_event_email_sent_at)->toBeNull();
});

it('does not send twice', function () {
    $event = endedWahRetreat();
    $event->update(['post_event_email_sent_at' => now()->subHour()]);

    SendRetreatThankYouEmailJob::dispatchSync($event);

    Mail::assertNotQueued(RetreatThankYouMailable::class);
});
