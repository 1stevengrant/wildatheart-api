<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    public function isAPIRequest(Request $request): bool
    {
        return str_starts_with($request->path(), 'api');
    }

    public function createInertiaErrorResponse(Request $request, HttpFoundationResponse $response): JsonResponse|HttpFoundationResponse
    {
        return inertia('Error', ['status' => $response->getStatusCode()])
            ->toResponse($request)
            ->setStatusCode($response->getStatusCode());
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json(['message' => 'Too many requests made. Try again in 1 minute.', 'type' => 'error'], 429);
        }

        $response = parent::render($request, $e);

        if ($this->isAPIRequest($request)) {
            return $response;
        }

        if ($response->getStatusCode() === 419) {
            return back()->with([
                'message' => 'The page expired, please try again.',
            ]);
        }

        if (in_array($response->getStatusCode(), [500, 503, 404, 403])) {
            return $this->createInertiaErrorResponse($request, $response);
        }

        return $response;
    }
}
