<?php

namespace App\Http\Middleware;

use Closure;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleteMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()->hasCompleteProfile()) {
            Inertia::share('show_profile_nudge', true);
        }

        return $next($request);
    }
}
