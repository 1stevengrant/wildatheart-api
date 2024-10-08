<?php

namespace Tests\Feature\Http\Controllers\API\V1\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\Fluent\AssertableJson;
use Statamic\Facades\Entry;
use Tests\TestCase;

uses(RefreshDatabase::class);


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
        ->data(['title' => 'Prayer 1', 'order' => 1]);
    $prayer1->save();

    $prayer2 = Entry::make()
        ->collection('prayers')
        ->slug('prayer-2')
        ->published(true)
        ->data(['title' => 'Prayer 2', 'order' => 2]);
    $prayer2->save();

    // Make the request
    $response = $this->getJson(route('content.v1.prayers.index'));

    // Assert the response
    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('0.slug', 'prayer-1')
                ->where('1.slug', 'prayer-2')
        );
});

// test('it can show a specific prayer', function () {
//     // Create a prayer entry
//     $prayer = Entry::make()
//         ->collection('prayers')
//         ->slug('prayer-1')
//         ->published(true)
//         ->data(['title' => 'Prayer 1']);
//     $prayer->save();

//     // Make the request
//     $response = $this->getJson('/api/v1/prayers/prayer-1');

//     // Assert the response
//     $response->assertStatus(200)
//         ->assertJson(
//             fn(AssertableJson $json) =>
//             $json->where('slug', 'prayer-1')
//                 ->where('title', 'Prayer 1')
//         );
// });
