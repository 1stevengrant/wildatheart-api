<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('shares the event social meta with the Inertia response so the client can keep tags in sync', function () {
    $event = Event::factory()->fires()->approved()->create();

    $response = $this->get(route('view.event', ['event' => $event]));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewEventPage')
            ->has('meta', fn (Assert $meta) => $meta
                ->where('url', route('view.event', ['event' => $event]))
                ->has('title')
                ->has('description')
                ->has('image')
            )
        );
});

it('shares the home page social meta', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('HomePage')
            ->has('meta', fn (Assert $meta) => $meta
                ->where('url', route('home'))
                ->where('title', 'Home')
                ->has('description')
                ->has('image')
            )
        );
});
