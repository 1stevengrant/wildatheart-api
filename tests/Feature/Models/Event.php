<?php

use App\Models\Event;

test('an event should not be indexed or searchable if approved_at is null', function () {
    $event = Event::factory()->create([
        'approved_at' => null,
    ]);

    expect($event->shouldBeSearchable())->toBeFalse();
});

test('an event should not be indexed or searchable if deleted_at is not null', function () {
    $event = Event::factory()->create([
        'deleted_at' => now(),
    ]);

    expect($event->shouldBeSearchable())->toBeFalse();
});

test('an event should be indexed and searchable if approved_at is not null and deleted_at is null', function () {
    $event = Event::factory()->create([
        'approved_at' => now(),
        'deleted_at' => null,
    ]);

    expect($event->shouldBeSearchable())->toBeTrue();
});
