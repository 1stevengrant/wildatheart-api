<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Mail\Event\RetreatThankYouMailable;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
});

it('uses the template for the event type with the correct feedback forms', function (string $eventTypeName, string $subject, string $hostFormId, string $attendeeFormId) {
    $host = User::factory()->create(['given_name' => 'John']);
    $event = Event::factory()->for($host)->approved()->createQuietly([
        'name' => 'Denver Retreat',
        'event_type_id' => EventType::where('name', $eventTypeName)->first()->id,
    ]);
    $emailTemplate = EmailTemplate::where('slug', RetreatThankYouMailable::templateSlugFor($event))->firstOrFail();

    $mailable = new RetreatThankYouMailable($event, $emailTemplate);

    $mailable->assertHasSubject($subject);
    $mailable->assertSeeInHtml('Hi John,', false);
    $mailable->assertSeeInHtml('Thank you for hosting a Denver Retreat', false);
    $mailable->assertSeeInHtml("https://ransomedheart.typeform.com/to/{$hostFormId}", false);
    $mailable->assertSeeInHtml("https://ransomedheart.typeform.com/to/{$attendeeFormId}", false);
})->with([
    [EventType::WAH_RETREAT, 'Thank you for hosting a Wild at Heart Retreat', 'BYGB5jOr', 'o2iSVZjZ'],
    [EventType::CAPTIVATING_CORE_RETREAT, 'Thank you for hosting a Captivating Retreat', 'jAYCEGqq', 'MYeXbfhl'],
    [EventType::CAPTIVATING_CORE_RETREAT_1_DAY, 'Thank you for hosting a Captivating Retreat', 'jAYCEGqq', 'MYeXbfhl'],
    [EventType::BECOMING_A_KING, 'Thank you for hosting a Becoming A King Retreat', 'SnlT2NIP', 'C4ddf7NT'],
    [EventType::SURVIVAL_SCHOOL, 'Thank you for hosting a Survival School', 'ta5VGDPO', 'Hpsxs7Ue'],
]);

it('greets all hosts when co-hosts are copied in', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $coHost = User::factory()->create(['given_name' => 'Jane']);
    $event = Event::factory()->wahRetreat()->for($host)->approved()->createQuietly();
    $event->managers()->attach($coHost);
    $emailTemplate = EmailTemplate::where('slug', 'wah-retreat-thank-you')->firstOrFail();

    $mailable = new RetreatThankYouMailable($event->fresh(), $emailTemplate);

    $mailable->assertSeeInHtml('Hi John and Jane,', false);
});

it('greets only the owner when co-hosts are not copied in', function () {
    $host = User::factory()->create(['given_name' => 'John']);
    $coHost = User::factory()->create(['given_name' => 'Jane']);
    $event = Event::factory()->wahRetreat()->for($host)->approved()->createQuietly();
    $event->managers()->attach($coHost);
    $emailTemplate = EmailTemplate::where('slug', 'wah-retreat-thank-you')->firstOrFail();
    $emailTemplate->cc_co_hosts = false;

    $mailable = new RetreatThankYouMailable($event->fresh(), $emailTemplate);

    $mailable->assertSeeInHtml('Hi John,', false);
    $mailable->assertDontSeeInHtml('Jane', false);
});
