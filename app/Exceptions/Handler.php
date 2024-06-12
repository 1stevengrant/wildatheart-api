<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

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

    public function isNotStatamicAPIRequest($request): bool
    {
        return !str_starts_with($request->path(), env('STATAMIC_API_ROUTE', 'content-api'));
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json(['message' => 'Too many requests made. Try again in 1 minute.', 'type' => 'error'], 429);
        }

        $response = parent::render($request, $e);

        if ($this->isNotStatamicAPIRequest($request) && !app()->environment(['local', 'testing']) && in_array($response->status(), [500, 503, 404, 403])) {
            return inertia('Error', ['status' => $response->status()])
                ->toResponse($request)
                ->setStatusCode($response->status());
        } elseif ($response->status() === 419) {
            return back()->with([
                'message' => 'The page expired, please try again.',
            ]);
        }

        activity()
            ->withProperties([
                'request' => $request,
                'response' => $response,
                'exception' => $e,
            ])
            ->log('error');

        return $response;
    }
}
