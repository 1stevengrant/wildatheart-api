<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAgreedToHostAgreementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()->hasAgreedToHostAgreement()) {
            session()->put('host_agreement_redirect_to', request()->route()->getName());

            return redirect()->route('host.agreement');
        }

        return $next($request);
    }
}
