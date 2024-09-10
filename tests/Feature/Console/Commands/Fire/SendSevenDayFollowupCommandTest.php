<?php

use App\Mail\Fire\SevenDayFollowupMailable;
use App\Models\EmailTemplate;
use App\Models\Event;

beforeEach(function () {
    $this->seed();
});

afterEach(function () {
    Event::truncate();
    EmailTemplate::truncate();
});

it('sends an email to a fire host last approved more than 90 days ago and last monthly email was 8 days ago', function () {
    Mail::fake();

    Event::factory()->fires()->create([
        'last_monthly_email_sent_at' => now()->subDays(8),
        'approved_at' => now()->subDays(91),
    ]);

    $this->artisan('app:send-seven-day-followup');

    Mail::assertQueued(SevenDayFollowupMailable::class);
});

it('does not send an email to a fire updated in the last 90 days', function () {
    Mail::fake();

    Event::factory()->fires()->create([
        'approved_at' => now()->subDays(89),
    ]);

    $this->artisan('app:send-seven-day-followup');

    Mail::assertNotQueued(SevenDayFollowupMailable::class);
});

it('does not send an email to those who have been contacted in the last 7 days', function () {
    Mail::fake();

    Event::factory()->fires()->create([
        'last_monthly_email_sent_at' => now()->subDays(6),
        'approved_at' => now()->subDays(8),
    ]);

    $this->artisan('app:send-seven-day-followup');

    Mail::assertNotQueued(SevenDayFollowupMailable::class);
});
