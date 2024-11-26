<?php

use App\Jobs\LogActivityJob;
use App\Models\Event;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
it('logs an activity in the database', function () {
    $user = User::factory()->create();
    $event = Event::factory()->fires()->create(['user_id' => $user->id]);

    $job = new LogActivityJob(
        description: 'Create Event',
        causedBy: $user,
        performedOn: $event,
    );

    $job->handle();

    $this->assertDatabaseHas('activity_log', [
        'description' => 'Create Event',
        'causer_id' => $user->id,
        'causer_type' => get_class($user),
        'subject_id' => $event->id,
        'subject_type' => get_class($event),
    ]);
});
