<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\ExperienceResponseBuilder;

class AllExperiencesController extends Controller
{
    public function __construct(private readonly ExperienceResponseBuilder $responseBuilder) {}

    public function __invoke(): JsonResponse
    {
        $experiences = EntryQueryBuilder::experiences()->get();

        $output = $experiences->map(
            fn ($experience) => $this->responseBuilder->buildBySlug($experience->slug)->toArray()
        );

        return response()->json($output);
    }
}
