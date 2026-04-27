<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ApiSpecController extends Controller
{
    public function __invoke(): Response
    {
        $path = resource_path('api-docs/openapi.yaml');
        $content = file_get_contents($path);

        return response($content, 200)
            ->header('Content-Type', 'application/x-yaml');
    }
}
