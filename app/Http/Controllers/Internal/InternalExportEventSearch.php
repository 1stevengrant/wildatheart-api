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
            criteria: $request->criteria(),
            fields: $request->fields(),
        );

        return back()->with(['notifications' => [
            'message' => 'Your export is being generated. You will receive an email shortly.',
        ]]);
    }
}
