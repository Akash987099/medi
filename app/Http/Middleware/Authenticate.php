<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        foreach ($request->route()?->gatherMiddleware() ?? [] as $middleware) {
            if (str_starts_with($middleware, 'auth:')) {
                $guards = explode(',', substr($middleware, strlen('auth:')));

                if (in_array('admin', $guards, true)) {
                    return route('admin.login');
                }

                if (in_array('user', $guards, true)) {
                    return route('login');
                }
            }
        }

        return route('login');
    }
}