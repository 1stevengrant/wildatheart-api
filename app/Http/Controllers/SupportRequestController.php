<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;

class SupportRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|max:255',
            'subject' => 'required|max:255',
            'description' => 'required',
        ]);

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
