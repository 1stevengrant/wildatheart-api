<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
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
