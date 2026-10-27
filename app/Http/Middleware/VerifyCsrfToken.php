<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $exception) {
            if (
                $request instanceof Request
                && $request->isMethod('post')
                && ($request->is('login') || $request->is('user/login'))
            ) {
                return redirect()
                    ->route('login')
                    ->with('login_expired', 'Your sign-in page expired. Please enter your credentials again.');
            }

            throw $exception;
        }
    }
}
