<?php

use App\Models\Campfire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->campfire = Campfire::factory()->create();
});

test('it allows a user to soft delete their own fire', function () {
    $this->actingAs($this->campfire->host)
        ->delete(route('delete.fire', $this->campfire));

    $this->assertSoftDeleted('campfires', [
        'id' => $this->campfire->id,
    ]);
});

test('a user can only delete their own fires', function () {
    $unauthorizedUser = \App\Models\User::factory()->create();

    $this->actingAs($unauthorizedUser)
        ->delete(route('delete.fire', $this->campfire))
        ->assertStatus(302)
        ->assertRedirect(route('home'))
        ->assertSessionHas('notifications.message', 'You are not authorized to delete this fire.');
});
