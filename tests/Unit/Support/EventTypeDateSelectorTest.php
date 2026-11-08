<?php

use App\Models\EventType;
use App\Support\EventTypeDateSelector;

it('requires a frequency for fires only', function () {
    expect(EventTypeDateSelector::requiresFrequency(EventType::TYPE_FIRE))->toBeTrue()
        ->and(EventTypeDateSelector::requiresFrequency(EventType::TYPE_EXPERIENCE))->toBeFalse()
        ->and(EventTypeDateSelector::requiresFrequency(EventType::TYPE_RETREAT))->toBeFalse()
        ->and(EventTypeDateSelector::requiresFrequency(null))->toBeFalse();
});

it('allows every frequency', function (?string $groupType) {
    expect(EventTypeDateSelector::allowedFrequencies($groupType))
        ->toBe(['biweekly', 'weekly', 'monthly', 'quarterly', 'varies']);
})->with([EventType::TYPE_FIRE, EventType::TYPE_EXPERIENCE]);
