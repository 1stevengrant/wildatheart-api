<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\ReadOnlyMode;
use App\Exceptions\ReadOnlyModeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns writes away before they reach a controller while read only mode is on.
 * Anything that slips through is still stopped by the database guard in
 * ReadOnlyMode, this just gives users a friendlier response.
 */
class EnforceReadOnlyMode
{
    private const array READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! ReadOnlyMode::isEnabled()) {
            return $next($request);
        }

        // Pennant's database store inserts the first time a feature resolves for a scope.
        config(['pennant.default' => 'array']);

        if (in_array($request->method(), self::READ_METHODS, true) && ! $this->isWritingRead($request)) {
            return $next($request);
        }

        if ($request->header('X-Inertia')) {
            return back()->with(['notifications' => [
                'message' => ReadOnlyMode::MESSAGE,
                'type' => 'error',
            ]]);
        }

        throw new ReadOnlyModeException;
    }

    /**
     * GET routes that write, or queue jobs that would be lost once the
     * database has moved.
     */
    private function isWritingRead(Request $request): bool
    {
        $controlPanel = config('statamic.cp.route');

        return $request->is(
            $controlPanel,
            "{$controlPanel}/*",
            'login',
            'callback',
            'logout',
            'fire/*/burn',
            'contacts/export*',
            'internal/event-search/export',
        );
    }
}
