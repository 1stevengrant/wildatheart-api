<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuccessAuth0CallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        return inertia('SuccessfulCallback');
    }
}
