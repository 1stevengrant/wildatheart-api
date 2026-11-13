<?php

use App\Models\Event;
use App\Models\EventType;

beforeEach(function () {
    EventType::query()->delete();
});

it('only gets active event types', function () {

    EventType::factory()->count(3)->create();

    $inactiveEventType = EventType::factory()->inactive()->create();

    $activeEventTypes = EventType::active()->get();

    expect($activeEventTypes->fresh()->count())->toBe(3)
        ->and($activeEventTypes->fresh()->contains($inactiveEventType))->toBeFalse();
});

describe('withPublicListingCounts', function () {
    beforeEach(function () {
        $this->eventType = EventType::factory()->create();
    });

    $counts = fn (EventType $eventType): EventType => EventType::query()
        ->select('event_types.*')
        ->withPublicListingCounts()
        ->findOrFail($eventType->id);

    it('counts approved public events and their distinct countries', function () use ($counts) {
        Event::factory()->approved()->count(2)->createQuietly(['event_type_id' => $this->eventType->id, 'country' => 'US', 'start_date' => now()->addWeek()]);
        Event::factory()->approved()->createQuietly(['event_type_id' => $this->eventType->id, 'country' => 'NZ', 'start_date' => now()->addWeek()]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(3)
            ->and($eventType->countries_count)->toBe(2);
    });

    it('ignores unapproved events', function () use ($counts) {
        Event::factory()->createQuietly(['event_type_id' => $this->eventType->id, 'approved_at' => null, 'start_date' => now()->addWeek()]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(0)
            ->and($eventType->countries_count)->toBe(0);
    });

    it('ignores private events', function () use ($counts) {
        Event::factory()->approved()->createQuietly(['event_type_id' => $this->eventType->id, 'is_private' => true, 'start_date' => now()->addWeek()]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(0)
            ->and($eventType->countries_count)->toBe(0);
    });

    it('ignores past events', function () use ($counts) {
        Event::factory()->approved()->createQuietly(['event_type_id' => $this->eventType->id, 'start_date' => now()->subDay()]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(0)
            ->and($eventType->countries_count)->toBe(0);
    });

    it('counts undated events', function () use ($counts) {
        Event::factory()->approved()->createQuietly(['event_type_id' => $this->eventType->id, 'start_date' => null]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(1)
            ->and($eventType->countries_count)->toBe(1);
    });

    it('counts events without a country as listings but not countries', function () use ($counts) {
        Event::factory()->approved()->createQuietly(['event_type_id' => $this->eventType->id, 'country' => null, 'start_date' => now()->addWeek()]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(1)
            ->and($eventType->countries_count)->toBe(0);
    });

    it('counts per event type', function () use ($counts) {
        $otherEventType = EventType::factory()->create();

        Event::factory()->approved()->createQuietly(['event_type_id' => $otherEventType->id, 'start_date' => now()->addWeek()]);

        $eventType = $counts($this->eventType);

        expect($eventType->listings_count)->toBe(0)
            ->and($eventType->countries_count)->toBe(0);
    });

    it('applies to inactive and ungrouped event types, leaving filtering to the caller', function () use ($counts) {
        $inactive = EventType::factory()->inactive()->create();
        $ungrouped = EventType::factory()->create(['type' => null]);

        Event::factory()->approved()->createQuietly(['event_type_id' => $inactive->id, 'start_date' => now()->addWeek()]);
        Event::factory()->approved()->createQuietly(['event_type_id' => $ungrouped->id, 'start_date' => now()->addWeek()]);

        expect($counts($inactive)->listings_count)->toBe(1)
            ->and($counts($ungrouped)->listings_count)->toBe(1);
    });
});
