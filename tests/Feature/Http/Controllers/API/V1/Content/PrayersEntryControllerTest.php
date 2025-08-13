<?php

namespace Tests\Feature\Http\Controllers\API\V1\Content;

use Statamic\Facades\Entry;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
it('returns 404 when page not found', function () {
    // Arrange
    $nonExistentSlug = 'non-existent-page';

    // Act
    $response = $this->getJson(route('content.v1.prayers.show', ['slug' => $nonExistentSlug]));

    // Assert
    $response->assertStatus(404)
        ->assertJson(['error' => 'Prayer not found']);
});

test('it can get a list of prayers', function () {
    // Create some prayer entries

    $prayer1 = Entry::make()
        ->collection('prayers')
        ->slug('prayer-1')
        ->published(true)
        ->data([
            'title' => 'Prayer 1',
            'content' => '<p>This is a test</p>',
            'summary' => 'This is a test summary',
            'drupal_url' => 'https://wildatheart.org/prayer/prayer-1',
        ]);

    $prayer1->save();

    $prayer2 = Entry::make()
        ->collection('prayers')
        ->slug('prayer-2')
        ->published(true)
        ->data([
            'title' => 'Prayer 2',
            'content' => '<p>This is a test</p>',
            'summary' => 'This is a test summary',
            'drupal_url' => 'https://wildatheart.org/prayer/prayer-2',
        ]);
    $prayer2->save();

    // Make the request
    $response = $this->getJson(route('content.v1.prayers.index'));

    // Assert the response
    $response->assertStatus(200)
        ->assertJsonCount(2);
    // Confirm that the title is the same
    $response->assertJsonFragment([
        'title' => 'Prayer 1',
    ]);
});

test('it can show a specific prayer', function () {
    // Create a prayer entry
    $prayer1 = Entry::make()
        ->collection('prayers')
        ->slug('prayer-1')
        ->published(true)
        ->data([
            'title' => 'Prayer 1',
            'content' => '<p>This is a test</p>',
            'summary' => 'This is a test summary',
            'drupal_url' => 'https://wildatheart.org/prayer/prayer-1',
        ]);

    $prayer1->save();

    // Make the request
    $response = $this->getJson(route('content.v1.prayers.show', ['slug' => $prayer1->slug]));

    // Assert the response
    $response->assertStatus(200);
    $response->assertJsonFragment([
        'title' => 'Prayer 1',
    ]);
});
