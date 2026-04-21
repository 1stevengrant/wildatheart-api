<?php

namespace App\Http\Controllers;

class APIDocsController extends Controller
{
    public function __invoke()
    {
        return view('docs.swagger');
    }
}
