<?php

namespace Tests\Feature\Exceptions;

use Inertia\Testing\AssertableInertia as Assert;

// test('Handles non-api routes', function () {
//     $exception = new \Exception('Something went wrong');

//     $response = $this->get('/test')->assertStatus(404);
//     $response->assertSee('Error');
// });

// // test('Handles api routes', function () {
// //     $response = $this->get('/api/content/test')->assertStatus(404);
// //     $response->assertSee('{"message":"Not found."}');
// // });

// test('Handles api routes', function () {
//     $response = $this->get(route('home'))->assertStatus(404);
//     // $response->assertSee('{"message":"Not found."}');
// });

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->approved()->fires()->create();
});

test('anyone can view an event', function () {
    // $this->withoutExceptionHandling();
    $this->get(route('view.event', $this->event))
        ->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('ViewEvent')
        );
});
