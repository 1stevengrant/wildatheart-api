<?php

use App\Models\EventType;
use App\Support\EventTypeDateSelector;

it('requires a frequency for fires and courses only', function () {
    expect(EventTypeDateSelector::requiresFrequency(EventType::TYPE_FIRE))->toBeTrue()
        ->and(EventTypeDateSelector::requiresFrequency(EventType::TYPE_EXPERIENCE))->toBeTrue()
        ->and(EventTypeDateSelector::requiresFrequency(EventType::TYPE_RETREAT))->toBeFalse()
        ->and(EventTypeDateSelector::requiresFrequency(null))->toBeFalse();
});

it('limits courses to weekly, biweekly, or varies', function () {
    expect(EventTypeDateSelector::allowedFrequencies(EventType::TYPE_EXPERIENCE))
        ->toBe(['weekly', 'biweekly', 'varies']);
});

it('allows every frequency for other groups', function () {
    expect(EventTypeDateSelector::allowedFrequencies(EventType::TYPE_FIRE))
        ->toBe(['biweekly', 'weekly', 'monthly', 'quarterly', 'varies']);
});
