<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Queries\MapEventTypesQuery;

class CreateSupportTicketController extends Controller
{
    public function __invoke(Request $request)
    {
        return inertia('SupportRequestPage', MapEventTypesQuery::get());
    }
}
