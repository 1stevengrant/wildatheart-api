<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = \App\Models\Event::factory()->create();
});
test('loads a list of my connections', function () {
    $this->actingAs($this->event->host)
        ->get(route('my.connections'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyConnections')
        );
});
