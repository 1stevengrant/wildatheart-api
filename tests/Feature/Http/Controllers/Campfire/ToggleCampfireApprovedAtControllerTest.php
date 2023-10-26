<?php

use App\Models\Campfire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->approvedCampfire = Campfire::factory()->approved()->create();
    $this->notApprovedCampfire = Campfire::factory()->create();
});

it('changes the approved at timestamp for a fire to be null', function () {
    $this->actingAs($this->approvedCampfire->user)
        ->put(route('toggle-campfire-approved-at', ['campfire' => $this->approvedCampfire]));

    $this->assertNull($this->approvedCampfire->fresh()->approved_at);
});

it('changes the approved at timestamp for a fire to be date', function () {
    $this->actingAs($this->notApprovedCampfire->user)
        ->put(route('toggle-campfire-approved-at', ['campfire' => $this->notApprovedCampfire]));

    expect($this->notApprovedCampfire->fresh()->approved_at)->toBeInstanceOf(Carbon::class);
});

test('only the owner of a fire can change the approved at timestamp', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->put(route('toggle-campfire-approved-at', ['campfire' => $this->approvedCampfire]))
        ->assertForbidden();
});

test('a user cannot see a fire that is unpublished', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get(route('view.fire', ['campfire' => $this->notApprovedCampfire]))
        ->assertStatus(404);
});

test('only the host can see their unpublished fire', function () {
    $this->actingAs($this->notApprovedCampfire->host)
        ->get(route('view.fire', ['campfire' => $this->notApprovedCampfire]))
        ->assertStatus(200);
});
