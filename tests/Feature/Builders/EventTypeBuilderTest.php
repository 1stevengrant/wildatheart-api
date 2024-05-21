<?php

use App\Models\EventType;

it('only gets active event types', function () {
    EventType::factory()->count(3)->create();
    $inactiveEventType = EventType::factory()->inactive()->create();

    $activeEventTypes = EventType::active()->get();

    expect($activeEventTypes->fresh()->count())->toBe(3)
        ->and($activeEventTypes->fresh()->contains($inactiveEventType))->toBeFalse();
})->skip();
