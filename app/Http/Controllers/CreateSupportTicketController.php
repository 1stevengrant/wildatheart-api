<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreateSupportTicketController extends Controller
{
    public function __invoke(Request $request)
    {
        return inertia('SupportRequestPage');
    }
}
