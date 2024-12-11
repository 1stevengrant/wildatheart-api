<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Jobs\LogActivityJob;
use App\Http\Requests\SupportRequest;

class SupportRequestController extends Controller
{
    public function __invoke(SupportRequest $request)
    {
        $request->validated();

        dispatch(new LogActivityJob(
            description: 'Issue created',
            causedBy: $request->user(),
            performedOn: $request->user(),
        ));

        Issue::create([
            'email' => request('email'),
            'name' => request('name'),
            'subject' => 'Fires Feedback: '.request('subject'),
            'description' => request('description'),
        ]);

        return redirect()->back()->with(['notifications' => [
            'message' => 'Issue reported successfully!',
            'type' => 'success',
        ],
        ]);
    }
}
