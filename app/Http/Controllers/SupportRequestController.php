<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Jobs\LogActivityJob;
use App\Http\Requests\SupportRequest;

class SupportRequestController extends Controller
{
    public function __invoke(SupportRequest $request)
    {
        LogActivityJob::dispatch(
            description: 'Issue created',
            causedBy: $request->user(),
            performedOn: $request->user(),
        );

        Issue::create([
            'email' => $request->validated('email'),
            'name' => mb_trim($request->validated('given_name') . ' ' . $request->validated('family_name')),
            'subject' => 'Fires Feedback: ' . $request->validated('subject'),
            'description' => $request->validated('description'),
        ]);

        return redirect()->back()->with(['notifications' => [
            'message' => 'Issue reported successfully!',
            'type' => 'success',
        ],
        ]);
    }
}
