<?php

use App\Console\Commands\Fire\SendMonthlyEmailToFireHostsCommand;
use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Models\Event;
use Illuminate\Support\Facades\Queue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    \App\Models\User::factory()->create([]);
    $this->seed();
});

it('dispatches email job for each fire event older than 90 days', function () {
    Queue::fake();

    $fires = Event::factory()->fires()->approved()->create([
        'updated_at' => now()->subDays(91),
    ]);

    $this->artisan(SendMonthlyEmailToFireHostsCommand::class);

    Queue::assertPushed(MonthlyFireCleanupEmailToHostJob::class, $fires->count());
})->skip();

it('does not dispatch email job when there are no fire events older than 90 days', function () {
    Queue::fake();

    Event::factory()->approved()->fires()->count(3)->create();

    $this->artisan(SendMonthlyEmailToFireHostsCommand::class);

    Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class);

})->skip();

it('does not dispatch email job for non-fire events', function () {
    Queue::fake();

    Event::factory()->approved()->wahExperiences()->create(['updated_at' => now()->subDays(91)]);

    $this->artisan(SendMonthlyEmailToFireHostsCommand::class);

    Queue::assertNotPushed(MonthlyFireCleanupEmailToHostJob::class);
})->skip();
