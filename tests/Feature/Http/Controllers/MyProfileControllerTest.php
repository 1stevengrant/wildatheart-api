<?php

use App\Models\Campfire;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->campfire = Campfire::factory()->create();
});
test('it shows my profile to be edited', function () {
    $this->actingAs($this->campfire->host)
        ->get(route('my.profile'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyProfile')
        );
});

it('only allows access to authorised user', function () {
    $this->get(route('my.profile'))
        ->assertRedirect(route('login'));
});
