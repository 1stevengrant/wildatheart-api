<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->approvedEvent = \App\Models\Event::factory()->approved()->fires()->create();
    $this->notApprovedEvent = \App\Models\Event::factory()->fires()->create();
});

it('changes the approved at timestamp for an event to be null', function () {
    $this->actingAs($this->approvedEvent->user)
        ->put(route('toggle-event-approved-at', ['event' => $this->approvedEvent]));

    $this->assertNull($this->approvedEvent->fresh()->approved_at);
});

it('changes the approved at timestamp for an event to be date', function () {
    $this->actingAs($this->notApprovedEvent->user)
        ->put(route('toggle-event-approved-at', ['event' => $this->notApprovedEvent]));

    expect($this->notApprovedEvent->fresh()->approved_at)->toBeInstanceOf(Carbon::class);
});

test('only the owner of an event can change the approved at timestamp', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->put(route('toggle-event-approved-at', ['event' => $this->approvedEvent]))
        ->assertForbidden();
});

test('a user cannot see an event that is unpublished', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get(route('view.event', ['event' => $this->notApprovedEvent]))
        ->assertRedirectToRoute('login');
});

test('only the host can see their unpublished event', function () {
    $this->withoutExceptionHandling();
    $this->actingAs($this->notApprovedEvent->host)
        ->get(route('view.event', ['event' => $this->notApprovedEvent]))
        ->assertStatus(200);
});
