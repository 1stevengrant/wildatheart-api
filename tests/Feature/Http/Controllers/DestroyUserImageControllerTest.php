<?php

use App\Models\Event;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->event = Event::factory()->fires()->create();
});

test('deletes a users profile photo', function () {
    // Mock both local and s3 disks since the model explicitly uses s3
    Storage::fake('local');
    Storage::fake('s3');

    $user = $this->event->user;
    $user->update(['image' => 'profile-photos/1234.jpg']);

    // Create a fake file to ensure it exists before deletion
    Storage::disk('s3')->put(
        'profile-photos/1234.jpg',
        'fake-content'
    );

    $response = $this->actingAs($user)
        ->delete(route('user.profile-photo.destroy'));

    $response->assertStatus(303)
        ->assertSessionHas('notifications.message', 'Profile photo deleted!');

    Storage::disk('s3')->assertMissing('profile-photos/1234.jpg');

    expect($user->fresh()->image)->toBeNull();
});

it('only allows access to authorised user', function () {
    $this->delete(route('user.profile-photo.destroy'))
        ->assertRedirect(route('login'));
});
