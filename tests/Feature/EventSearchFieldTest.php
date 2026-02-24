<?php

use App\Models\Event;
use App\Enums\Internal\EventSearchField;

it('returns start_date as a string without calling format()', function () {
    $event = new Event;
    $event->setRawAttributes(['start_date' => '2026-03-12 00:00:00']);

    $value = EventSearchField::StartDate->getValue($event);

    expect($value)->toBeString()
        ->and($value)->toBe('2026-03-12');
});

it('returns null for start_date when not set', function () {
    $event = new Event;
    $event->setRawAttributes(['start_date' => null]);

    $value = EventSearchField::StartDate->getValue($event);

    expect($value)->toBeNull();
});
