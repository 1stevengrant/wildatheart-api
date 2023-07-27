<?php

use App\Actions\Campfire\CheckWelcomeStatus;
use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;

it('sends the welcome email', function () {
    Mail::fake();

    $campfire = Campfire::factory()->create([
        'welcome_sent' => false,
    ]);

    $this->assertFalse($campfire->welcome_sent);

    CheckWelcomeStatus::run(campfire: $campfire);

    $campfire->refresh();

    $this->assertTrue((bool) $campfire->welcome_sent);

    Mail::assertQueued(SendWelcomeEmail::class);
});
