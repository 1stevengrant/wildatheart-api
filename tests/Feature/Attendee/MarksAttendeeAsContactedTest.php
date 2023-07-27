<?php

use App\Http\Livewire\MyConnections;
use App\Models\Attendee;
use App\Models\Campfire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);
it('marks an attendee as contacted', function () {
    Notification::fake();

    $user = User::factory()->create();
    $campfire = Campfire::factory()->create([
        'user_id' => $user->id,
    ]);
    $attendee = Attendee::factory()->create([
        'campfire_id' => $campfire->id,
    ]);
    $this->actingAs($user);
    livewire(MyConnections::class)
        ->call('toggleContactedStatus', $attendee);

    expect($attendee->fresh()->contacted_on)->not->toBeNull();
});

it('marks an attendee as not contacted', function () {
    Notification::fake();

    $user = User::factory()->create();
    $campfire = Campfire::factory()->create([
        'user_id' => $user->id,
    ]);
    $attendee = Attendee::factory()->create([
        'campfire_id' => $campfire->id,
        'contacted_on' => now(),
    ]);
    $this->actingAs($user);
    livewire(MyConnections::class)
        ->call('toggleContactedStatus', $attendee);

    expect($attendee->fresh()->contacted_on)->toBeNull();
});
