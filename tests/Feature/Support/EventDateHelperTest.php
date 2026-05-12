<?php

use Carbon\Carbon;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Support\EventDateHelper;

it('adds the end date for a retreat event type', function () {
    $request = new Request(['start_date' => '2002-02-02']);

    EventDateHelper::adjustEndDateForRetreat($request, EventType::TYPE_RETREAT, EventType::WAH_RETREAT);

    $expectedEndDate = Carbon::createFromFormat('Y-m-d', '2002-02-02')->addDays(3)->format('Y-m-d');
    expect($request->end_date)->toBe($expectedEndDate);
});

it('does not add an end date for non-retreat event types', function (string $groupType) {
    $request = new Request(['start_date' => '2002-02-02']);

    EventDateHelper::adjustEndDateForRetreat($request, $groupType, 'Test Event Type');

    expect($request->end_date)->toBeNull();
})->with([
    EventType::TYPE_FIRE,
    EventType::TYPE_EXPERIENCE,
]);
