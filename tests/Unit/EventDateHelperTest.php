<?php

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Support\EventDateHelper;

it('adds 3 days to regular retreat end date', function () {
    $request = new Request([
        'start_date' => '2024-01-15',
    ]);

    EventDateHelper::adjustEndDateForRetreat(
        request: $request,
        groupType: EventType::TYPE_RETREAT,
        eventTypeName: EventType::WAH_RETREAT
    );

    expect($request->end_date)->toBe('2024-01-18');
});

it('adds 1 day to 1-day captivating retreat end date', function () {
    $request = new Request([
        'start_date' => '2024-01-15',
    ]);

    EventDateHelper::adjustEndDateForRetreat(
        request: $request,
        groupType: EventType::TYPE_RETREAT,
        eventTypeName: EventType::CAPTIVATING_CORE_RETREAT_1_DAY
    );

    expect($request->end_date)->toBe('2024-01-16');
});

it('does not modify end date for non-retreat events', function () {
    $request = new Request([
        'start_date' => '2024-01-15',
    ]);

    EventDateHelper::adjustEndDateForRetreat(
        request: $request,
        groupType: EventType::TYPE_EXPERIENCE,
        eventTypeName: EventType::WAH_EXPERIENCE
    );

    expect($request->has('end_date'))->toBeFalse();
});

it('adds 3 days to regular captivating retreat', function () {
    $request = new Request([
        'start_date' => '2024-01-15',
    ]);

    EventDateHelper::adjustEndDateForRetreat(
        request: $request,
        groupType: EventType::TYPE_RETREAT,
        eventTypeName: EventType::CAPTIVATING_CORE_RETREAT
    );

    expect($request->end_date)->toBe('2024-01-18');
});
