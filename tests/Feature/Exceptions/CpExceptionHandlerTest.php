<?php

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Exceptions\CpExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Statamic\Exceptions\ControlPanelExceptionHandler;

it('binds the custom CP exception handler', function () {
    expect(app(ControlPanelExceptionHandler::class))->toBeInstanceOf(CpExceptionHandler::class);
});

it('redirects CP authorization failures to the app login route', function () {
    $request = Request::create('/cp/taxonomies/topics/edit', 'GET');
    app()->instance('request', $request);

    $handler = app(ControlPanelExceptionHandler::class);

    $response = $handler->render($request, new AuthorizationException('This action is unauthorized.'));

    expect($response)
        ->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('login'));
});
