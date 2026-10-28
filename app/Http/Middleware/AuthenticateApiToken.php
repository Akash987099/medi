<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserApiDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = str_contains($token, '|')
            ? Auth::guard('sanctum')->user()
            : null;

        $user ??= User::query()
            ->whereIn('role', [1, 2])
            ->whereNotNull('api_token')
            ->cursor()
            ->first(static fn (User $candidate): bool => hash_equals($candidate->api_token, $token));

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');
        $originHost = $origin ? parse_url($origin, PHP_URL_HOST) : null;
        if (
            ! is_string($originHost)
            || ! UserApiDomain::query()
                ->where('user_id', $user->id)
                ->where('domain_hash', hash('sha256', strtolower(rtrim($originHost, '.'))))
                ->where('is_active', true)
                ->exists()
        ) {
            return response()->json(['message' => 'This API token is not authorized for the request domain.'], 403);
        }

        $request->setUserResolver(static fn (?string $guard = null): User => $user);

        return $next($request);
    }
}
