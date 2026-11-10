<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For test tools that change real data. They answer 404 in production, as if they
 * didn't exist.
 */
class BlockInProductionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(app()->isProduction(), 404);

        return $next($request);
    }
}
