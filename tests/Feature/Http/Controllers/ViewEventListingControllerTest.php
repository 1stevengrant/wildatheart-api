<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->event = Event::factory()->approved()->fires()->create();
});

test('anyone can view an event', function () {
    $this->withoutExceptionHandling();

    $this->get(route('view.event', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewEventPage')
        );
});

test('when viewing an event as a logged in user, I can view the event', function () {
    // this test was needed as the controller is checking for auth()->user() and not auth()->user()->id
    // when it dispatches a job to log the activity. The activity logger expects a user model and not an integer.
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('view.event', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewEventPage')
        );
});

test('viewing an event logs who viewed it without copying the event or user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('view.event', $this->event))
        ->assertStatus(200);

    Bus::assertDispatched(LogActivityJob::class, fn (LogActivityJob $job): bool => $job->properties === []
        && $job->performedOn->is($this->event)
        && $job->causedBy->is($user));
});
