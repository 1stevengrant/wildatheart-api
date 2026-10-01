<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Data\V1\EventInterestData;
use App\Http\Controllers\Controller;
use App\Actions\Event\RemoveEventInterest;
use App\Data\V1\RegisterEventInterestData;
use App\Actions\Event\RegisterEventInterest;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EventInterestController extends Controller
{
    public function show(string $event): JsonResponse
    {
        $interest = $this->user()->interestIn($event);

        if (! $interest) {
            return response()->json(['message' => 'Not Found.'], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->json(EventInterestData::fromPivot($interest));
    }

    public function store(string $event, RegisterEventInterestData $data): JsonResponse
    {
        $interest = RegisterEventInterest::run($this->user(), $event, $data);

        return response()->json(EventInterestData::fromPivot($interest));
    }

    public function destroy(string $event): Response
    {
        RemoveEventInterest::run($this->user(), $event);

        return response()->noContent();
    }
}
