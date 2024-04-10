<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = Event::factory()->fires()->create();
});
test('it shows my profile to be edited', function () {
    $this->actingAs($this->event->host)
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
