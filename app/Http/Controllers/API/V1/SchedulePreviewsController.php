<?php

namespace App\Http\Controllers\API\V1;

use Carbon\CarbonImmutable;
use App\Data\SchedulePreviewData;
use Illuminate\Http\JsonResponse;
use App\Enums\Event\FrequencyEnum;
use App\Http\Controllers\Controller;
use App\Support\Schedule\ScheduleDateGenerator;
use App\Http\Requests\API\V1\StoreSchedulePreviewRequest;

class SchedulePreviewsController extends Controller
{
    public function store(StoreSchedulePreviewRequest $request): JsonResponse
    {
        return response()->json(SchedulePreviewData::forAnchor(
            frequency: FrequencyEnum::from($request->validated('frequency')),
            anchor: CarbonImmutable::parse($request->validated('anchor_date')),
            count: (int) ($request->validated('count') ?? ScheduleDateGenerator::RENEWAL_COUNT),
        ));
    }
}
