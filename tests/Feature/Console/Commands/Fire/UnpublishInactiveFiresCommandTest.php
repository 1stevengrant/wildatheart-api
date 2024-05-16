<?php

use App\Models\EmailTemplate;
use App\Models\Event;

beforeEach(function () {
    $this->seed();

    $this->emailTemplate = EmailTemplate::factory()->create([
        'name' => 'Fire deactivating soon',
    ]);
});

afterEach(function () {
    Event::truncate();
    EmailTemplate::truncate();
});

it('unpublishes fires where last monthly email was more than 10 days ago and last approved was  ', function () {
    $fire = Event::factory()->fires()->create([
        'last_monthly_email_sent_at' => now()->subDays(11),
        'approved_at' => now()->subDays(91),
    ]);

    $secondFire = Event::factory()->fires()->create([
        'last_monthly_email_sent_at' => now()->subDays(11),
        'approved_at' => now()->subDays(89),
    ]);

    $this->artisan('app:unpublish-inactive-fires');

    expect($fire->refresh()->approved_at)->toBeNull()
        ->and($secondFire->refresh()->approved_at)->not->toBeNull();
});
