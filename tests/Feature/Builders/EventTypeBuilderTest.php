<?php

use App\Models\EventType;

it('only gets active event types', function () {
    $eventTypes = EventType::factory()->count(3)->create();
    $inactiveEventType = EventType::factory()->inactive()->create();

    $activeEventTypes = EventType::active()->get();

    expect($activeEventTypes->count())->toBe(3)
        ->and($activeEventTypes->contains($inactiveEventType))->toBeFalse();
});
