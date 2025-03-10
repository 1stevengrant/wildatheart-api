<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    \App\Models\User::factory()->create();
    $this->event = Event::factory()->fires()->create();
});

test('it shows my profile to be edited', function () {
    $this->actingAs($this->event->user)
        ->get(route('my.profile'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyProfilePage')
        );
});

it('only allows access to authorised user', function () {
    $this->get(route('my.profile'))
        ->assertRedirect(route('login'));
});
