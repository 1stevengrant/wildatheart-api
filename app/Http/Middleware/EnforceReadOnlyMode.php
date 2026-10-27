<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\ReadOnlyMode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects writes, and the GET routes that write, while read only mode is on.
 */
class EnforceReadOnlyMode
{
    private const array READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! ReadOnlyMode::isEnabled()) {
            return $next($request);
        }

        if (in_array($request->method(), self::READ_METHODS, true) && ! $this->isWritingRead($request)) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => ReadOnlyMode::MESSAGE], Response::HTTP_SERVICE_UNAVAILABLE, ['Retry-After' => ReadOnlyMode::RETRY_AFTER_SECONDS]);
        }

        if ($request->header('X-Inertia')) {
            return back()->with(['notifications' => [
                'message' => ReadOnlyMode::MESSAGE,
                'type' => 'error',
            ]]);
        }

        abort(Response::HTTP_SERVICE_UNAVAILABLE, ReadOnlyMode::MESSAGE, ['Retry-After' => ReadOnlyMode::RETRY_AFTER_SECONDS]);
    }

    private function isWritingRead(Request $request): bool
    {
        $controlPanel = config('statamic.cp.route');

        return $request->is($controlPanel, "{$controlPanel}/*", 'login', 'callback', 'logout', 'fire/*/burn');
    }
}
