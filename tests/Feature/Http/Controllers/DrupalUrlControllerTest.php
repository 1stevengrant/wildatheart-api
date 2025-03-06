<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    // Mock the authenticated user
    $this->user = \App\Models\User::factory()->create();
    $this->actingAs($this->user);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('redirects legacy Drupal URL to Laravel URL and logs activity', function () {

    $response = $this->get('/drupal');

    // Assert the response redirects to the home route
    $response->assertRedirect(route('home'));

    // Assert the activity was logged correctly
    $this->assertDatabaseHas('activity_log', [
        'description' => 'Redirected legacy Drupal URL to Laravel URL: drupal',
        'causer_id' => $this->user->id,
    ]);
});

it('logs activity and redirects for legacy Drupal URLs', function () {

    // Act as the user and make a GET request to the /drupal/{any} route
    $response = $this->get('/drupal/some/legacy/path');

    // Assert the response redirects to the home route
    $response->assertRedirect(route('home'));

    // Assert the activity log was created with the correct description and user
    $this->assertDatabaseHas('activity_log', [
        'description' => 'Redirected legacy Drupal URL to Laravel URL: drupal/some/legacy/path',
        'causer_id' => $this->user->id,
    ]);
});

it('logs activity and redirects to home when event is not found', function () {
    // Make a GET request to the /drupal/{type}/{legacyDrupalUrl} route
    $response = $this->get('/drupal/some-type/some-legacy-url');

    // Assert the response redirects to the home route
    $response->assertRedirect(route('home'));

    // Assert the activity log was created with the correct description and user
    $this->assertDatabaseHas('activity_log', [
        'description' => 'Redirected legacy Drupal URL to Laravel URL: drupal/some-type/some-legacy-url',
        'causer_id' => $this->user->id,
    ]);
});

it('logs activity and redirects to the event when event is found', function () {
    $this->withoutExceptionHandling();
    $legacyDrupalUrl = '/some-legacy-url';
    // Mock the Event model to return an event for the legacy URL
    $event = \App\Models\Event::factory()->fires()->create([
        'legacy_drupal_url' => $legacyDrupalUrl,
    ]);

    // Make a GET request to the /drupal/{legacyDrupalUrl} route
    $response = $this->get('/drupal' . $legacyDrupalUrl);

    // Assert the response redirects to the event's view route
    $response->assertRedirect(route('view.event', $event));
});

it('logs activity and redirects to event type when course matches', function () {
    // Define the event type mapping and expected event type
    $course = 'basic';
    $eventType = \App\Models\EventType::factory()->create(['name' => \App\Models\EventType::WAH_RETREAT]);

    // Make a GET request to the /drupal/courses/{course} route
    $response = $this->get('/drupal/courses/' . $course);

    // Assert the response redirects to the event type's index route
    $response->assertRedirect(route('host.eventType.index', $eventType));

    // Assert the activity log was created with the correct description and user
    $this->assertDatabaseHas('activity_log', [
        'description' => 'Redirected legacy Drupal URL to Laravel URL: drupal/courses/' . $course,
        'causer_id' => $this->user->id,
    ]);
});

it('logs activity and redirects to home when course does not match', function () {
    // Define a non-existent course
    $course = 'unknown-course';

    // Make a GET request to the /drupal/courses/{course} route
    $response = $this->get('/drupal/courses/' . $course);

    // Assert the response redirects to the home route
    $response->assertRedirect(route('home'));

    // Assert the activity log was created with the correct description and user
    $this->assertDatabaseHas('activity_log', [
        'description' => 'Redirected legacy Drupal URL to Laravel URL: drupal/courses/' . $course,
        'causer_id' => $this->user->id,
    ]);
});
