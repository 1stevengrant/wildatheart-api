<?php

use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->user = \App\Models\User::factory()->create();
    $this->actingAs($this->user);
});

it('redirects legacy Drupal URL to Laravel URL and logs activity', function () {
    $response = $this->get('/drupal');

    $response->assertRedirect(route('home'));

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) {
        return $job->description === 'Redirected legacy Drupal URL to Laravel URL: drupal'
            && $job->causedBy->is($this->user);
    });
});

it('logs activity and redirects for legacy Drupal URLs', function () {
    $response = $this->get('/drupal/some/legacy/path');

    $response->assertRedirect(route('home'));

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) {
        return $job->description === 'Redirected legacy Drupal URL to Laravel URL: drupal/some/legacy/path'
            && $job->causedBy->is($this->user);
    });
});

it('logs activity and redirects to home when event is not found', function () {
    $response = $this->get('/drupal/some-type/some-legacy-url');

    $response->assertRedirect(route('home'));

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) {
        return $job->description === 'Redirected legacy Drupal URL to Laravel URL: drupal/some-type/some-legacy-url'
            && $job->causedBy->is($this->user);
    });
});

it('logs activity and redirects to the event when event is found', function () {
    $this->withoutExceptionHandling();
    $legacyDrupalUrl = '/some-legacy-url';
    $event = \App\Models\Event::factory()->fires()->create([
        'legacy_drupal_url' => $legacyDrupalUrl,
    ]);

    $response = $this->get('/drupal' . $legacyDrupalUrl);

    $response->assertRedirect(route('view.event', $event));

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($legacyDrupalUrl) {
        return $job->description === 'Redirected legacy Drupal URL to Laravel URL: drupal' . $legacyDrupalUrl
            && $job->causedBy->is($this->user);
    });
});

it('logs activity and redirects to event type when course matches', function () {
    $course = 'basic';
    $eventType = \App\Models\EventType::factory()->create(['name' => \App\Models\EventType::WAH_RETREAT]);

    $response = $this->get('/drupal/courses/' . $course);

    $response->assertRedirect(route('host.eventType.index', $eventType));

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($course) {
        return $job->description === 'Redirected legacy Drupal URL to Laravel URL: drupal/courses/' . $course
            && $job->causedBy->is($this->user);
    });
});

it('logs activity and redirects to home when course does not match', function () {
    $course = 'unknown-course';

    $response = $this->get('/drupal/courses/' . $course);

    $response->assertRedirect(route('home'));

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($course) {
        return $job->description === 'Redirected legacy Drupal URL to Laravel URL: drupal/courses/' . $course
            && $job->causedBy->is($this->user);
    });
});
