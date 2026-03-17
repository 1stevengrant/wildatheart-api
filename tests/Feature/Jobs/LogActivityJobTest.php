<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
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
