<?php

use App\Models\Event;
use Illuminate\Support\Carbon;
use StatamicRadPack\Runway\Runway;

it('reports the published status based on the approved at date', function () {
    expect(Event::factory()->approved()->create()->is_published)->toBeTrue()
        ->and(Event::factory()->create()->is_published)->toBeFalse();
});

it('sets the approved at date when an unpublished event is published', function () {
    $this->freezeSecond();

    $event = Event::factory()->create();

    $event->update(['is_published' => true]);

    expect($event->fresh()->approved_at->toDateTimeString())->toBe(now()->toDateTimeString());
});

it('keeps the original approved at date when a published event is saved as published', function () {
    $approvedAt = Carbon::parse('2025-01-15 10:30:00');

    $event = Event::factory()->create(['approved_at' => $approvedAt]);

    $event->update(['is_published' => true]);

    expect($event->fresh()->approved_at->toDateTimeString())->toBe($approvedAt->toDateTimeString());
});

it('clears the approved at date when a published event is unpublished', function () {
    $event = Event::factory()->approved()->create();

    $event->update(['is_published' => false]);

    expect($event->fresh()->approved_at)->toBeNull();
});

it('exposes a non queryable published toggle on the runway event blueprint', function () {
    $field = Runway::findResource('event')->blueprint()->field('is_published');

    expect($field)->not->toBeNull()
        ->and($field->type())->toBe('toggle')
        ->and($field->isSortable())->toBeFalse()
        ->and($field->isFilterable())->toBeFalse();
});
