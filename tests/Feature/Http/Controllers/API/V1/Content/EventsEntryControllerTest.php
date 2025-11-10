<?php

namespace Tests\Feature\Http\Controllers\API\V1\Content;

use Statamic\Facades\Entry;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('returns 404 when event not found', function () {
    // Arrange
    $nonExistentSlug = 'non-existent-event';

    // Act
    $response = $this->getJson("/api/content/v1/events/{$nonExistentSlug}");

    // Assert
    $response->assertStatus(404)
        ->assertJson(['error' => 'Event not found']);
});

test('it can get a list of events', function () {
    // Create some event entries

    $event1 = Entry::make()
        ->collection('events')
        ->slug('event-1')
        ->published(true)
        ->blueprint('video_study')
        ->data([
            'title' => 'Event 1',
            'content' => '<p>This is a test event</p>',
            'summary' => 'This is a test summary',
            'drupal_url' => 'https://wildatheart.org/event/event-1',
        ]);

    $event1->save();

    $event2 = Entry::make()
        ->collection('events')
        ->slug('event-2')
        ->published(true)
        ->blueprint('video_study')
        ->data([
            'title' => 'Event 2',
            'content' => '<p>This is another test event</p>',
            'summary' => 'This is another test summary',
            'drupal_url' => 'https://wildatheart.org/event/event-2',
        ]);
    $event2->save();

    // Make the request
    $response = $this->getJson('/api/content/v1/events');

    // Assert the response
    $response->assertStatus(200)
        ->assertJsonCount(2);
    // Confirm that the title is the same
    $response->assertJsonFragment([
        'title' => 'Event 1',
    ]);
});

test('it can show a specific event', function () {
    // Create an event entry
    $event1 = Entry::make()
        ->collection('events')
        ->slug('event-1')
        ->published(true)
        ->blueprint('video_study')
        ->data([
            'title' => 'Event 1',
            'content' => '<p>This is a test event</p>',
            'summary' => 'This is a test summary',
            'drupal_url' => 'https://wildatheart.org/event/event-1',
        ]);

    $event1->save();

    // Make the request
    $response = $this->getJson("/api/content/v1/events/{$event1->slug}");

    // Assert the response
    $response->assertStatus(200);
    $response->assertJsonFragment([
        'title' => 'Event 1',
    ]);
});
