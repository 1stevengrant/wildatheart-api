<?php

namespace App\Http\Controllers;

class APIDocsController extends Controller
{
    public function index()
    {
        return view('docs.swagger');
    }

    public function spec()
    {
        $path = resource_path('api-docs/openapi.yaml');
        $content = file_get_contents($path);

        return response($content, 200)
            ->header('Content-Type', 'application/x-yaml');
    }
}
