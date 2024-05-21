<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportRequest;
use App\Models\Issue;

class SupportRequestController extends Controller
{
    public function __invoke(SupportRequest $request)
    {
        $request->validated();

        activity()->log('Create Issue');

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
