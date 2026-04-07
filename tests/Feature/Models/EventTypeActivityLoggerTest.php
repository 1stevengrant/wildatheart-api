<?php

use App\Models\User;
use App\Models\EventType;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('logs activity when creating an event type', function () {
    $user = User::factory()->create(['given_name' => 'John', 'family_name' => 'Doe']);
    actingAs($user);

    $eventType = EventType::create([
        'name' => 'Test Event',
        'type' => EventType::TYPE_RETREAT,
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'event_types',
        'description' => 'Event type has been created by John Doe',
        'subject_type' => EventType::class,
        'subject_id' => $eventType->id,
        'causer_id' => $user->id,
        'properties->attributes->name' => 'Test Event',
        'properties->attributes->is_active' => true,
    ]);

});

it('logs activity when updating an event type', function () {
    $user = User::factory()->create(['given_name' => 'Jane', 'family_name' => 'Smith']);
    $eventType = EventType::factory()->create(['name' => 'Original Name']);

    // Clear the activity log after factory creation
    Activity::query()->delete();

    $this->actingAs($user);

    $eventType->update([
        'name' => 'Updated Name',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'event_types',
        'description' => 'Event type has been updated by Jane Smith',
        'subject_type' => EventType::class,
        'subject_id' => $eventType->id,
        'causer_id' => $user->id,
        'properties->old->name' => 'Original Name',
        'properties->attributes->name' => 'Updated Name',
    ]);
});

it('does not log activity when no attributes have changed', function () {
    $user = User::factory()->create();
    $eventType = EventType::factory()->create(['name' => 'Test Event']);

    actingAs($user);

    $eventType->update(['name' => 'Test Event']); // Same value

    expect(Activity::count())->toBe(2); // 2 created by the factory, 1 for the update
});

it('logs system changes when no user is authenticated', function () {
    $eventType = EventType::create([
        'name' => 'System Created Event',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'event_types',
        'description' => 'Event type has been created by System',
        'subject_type' => EventType::class,
        'subject_id' => $eventType->id,
        'causer_id' => null,
        'properties->attributes->name' => 'System Created Event',
    ]);
});
