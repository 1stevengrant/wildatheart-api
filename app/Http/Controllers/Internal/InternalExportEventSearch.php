<?php

namespace App\Http\Controllers\Internal;

use App\Jobs\ExportEventSearchJob;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Internal\InternalEventSearchRequest;

class InternalExportEventSearch extends Controller
{
    public function __invoke(InternalEventSearchRequest $request): RedirectResponse
    {
        ExportEventSearchJob::dispatch(
            user: auth()->user(),
            lat: $request->hasSearchCoordinates() ? $request->lat() : null,
            lng: $request->hasSearchCoordinates() ? $request->lng() : null,
            radius: $request->hasSearchCoordinates() ? $request->radius() : null,
            eventTypeIds: $request->eventTypeIds(),
            fields: $request->fields(),
            activeOnly: $request->activeOnly(),
            includeDeleted: $request->includeDeleted(),
            createdFrom: $request->createdFrom(),
            createdTo: $request->createdTo(),
        );

        return back()->with(['notifications' => [
            'message' => 'Your export is being generated. You will receive an email shortly.',
        ]]);
    }
}
