<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use App\Jobs\HostContactsExportJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\ExportHostContactsRequest;

class HostContactsExportController extends Controller
{
    public function __invoke(ExportHostContactsRequest $request): JsonResponse
    {
        HostContactsExportJob::dispatch(
            hostId: $this->user()->id,
            filters: $request->filters(),
            selectedContacts: $request->selectedContacts(),
        );

        return response()->json([
            'message' => 'Your contacts export is being generated. You will receive an email shortly.',
        ]);
    }
}
