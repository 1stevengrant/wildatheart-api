<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->approvedEvent = Event::factory()->approved()->fires()->create();
    $this->notApprovedEvent = Event::factory()->fires()->create();
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

test('a non-owner and non-co-manager cannot change the approved at timestamp', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->put(route('toggle-event-approved-at', ['event' => $this->approvedEvent]))
        ->assertForbidden();
});

test('a co-manager can change the approved at timestamp', function () {
    $coManager = User::factory()->create();
    $this->approvedEvent->managers()->attach($coManager->id);

    $this->actingAs($coManager)
        ->put(route('toggle-event-approved-at', ['event' => $this->approvedEvent]));

    $this->assertNull($this->approvedEvent->fresh()->approved_at);
});

test('a user cannot see an event that is unpublished', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get(route('view.event', ['event' => $this->notApprovedEvent]))
        ->assertRedirectToRoute('login');
});

test('only the host can see their unpublished event', function () {
    $this->withoutExceptionHandling();
    $this->actingAs($this->notApprovedEvent->user)
        ->get(route('view.event', ['event' => $this->notApprovedEvent]))
        ->assertStatus(200);
});

test('a co-manager can see an unpublished event', function () {
    $this->withoutExceptionHandling();
    $coManager = User::factory()->create();
    $this->notApprovedEvent->managers()->attach($coManager->id);

    $this->actingAs($coManager)
        ->get(route('view.event', ['event' => $this->notApprovedEvent]))
        ->assertStatus(200);
});
