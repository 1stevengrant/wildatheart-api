<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->user = User::factory()->create();
    $this->newStartDate = now()->addDays(20)->format('Y-m-d');
});

test('updating a 1-Day Captivating Retreat keeps the duration at 1 day', function () {
    $eventType = EventType::where('name', EventType::CAPTIVATING_CORE_RETREAT_1_DAY)->first();
    $event = Event::factory()->for($this->user)->createQuietly([
        'event_type_id' => $eventType->id,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(5),
    ]);

    $this->actingAs($this->user)->put(route('update.event', $event), [
        'name' => $event->name,
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'start_date' => $this->newStartDate,
    ]);

    $event->refresh();
    $startDate = Carbon::parse($this->newStartDate);
    expect($event->end_date->format('Y-m-d'))->toBe($startDate->format('Y-m-d'));
});

test('updating a multi-day retreat sets the end date 3 days after the start date', function () {
    $eventType = EventType::where('name', EventType::CAPTIVATING_CORE_RETREAT)->first();
    $event = Event::factory()->for($this->user)->createQuietly([
        'event_type_id' => $eventType->id,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(8),
    ]);

    $this->actingAs($this->user)->put(route('update.event', $event), [
        'name' => $event->name,
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'start_date' => $this->newStartDate,
    ]);

    $event->refresh();
    $startDate = Carbon::parse($this->newStartDate);
    expect($event->end_date->format('Y-m-d'))->toBe($startDate->copy()->addDays(3)->format('Y-m-d'));
});

test('updating an experience persists the is_private toggle', function () {
    $event = Event::factory()->for($this->user)->captivatingExperience()->createQuietly([
        'is_private' => false,
    ]);

    $this->actingAs($this->user)->put(route('update.event', $event), [
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'start_date' => $this->newStartDate,
        'is_private' => true,
    ]);

    expect($event->refresh()->is_private)->toBeTrue();
});

test('updating a retreat persists the is_private toggle', function () {
    $eventType = EventType::where('name', EventType::CAPTIVATING_CORE_RETREAT)->first();
    $event = Event::factory()->for($this->user)->createQuietly([
        'event_type_id' => $eventType->id,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(8),
        'is_private' => false,
    ]);

    $this->actingAs($this->user)->put(route('update.event', $event), [
        'name' => $event->name,
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'start_date' => $this->newStartDate,
        'is_private' => true,
    ]);

    expect($event->refresh()->is_private)->toBeTrue();

    $this->actingAs($this->user)->put(route('update.event', $event), [
        'name' => $event->name,
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'start_date' => $this->newStartDate,
        'is_private' => false,
    ]);

    expect($event->refresh()->is_private)->toBeFalse();
});
