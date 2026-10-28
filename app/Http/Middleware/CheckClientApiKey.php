<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserApiDomain;

class CheckClientApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY') ?: $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'error'   => true,
                'message' => 'API key missing'
            ], 401);
        }

        $apiKey = trim($apiKey);
        $client = User::query()
            ->whereIn('role', [1, 2])
            ->whereNotNull('api_token')
            ->cursor()
            ->first(static fn (User $user): bool => hash_equals($user->api_token, $apiKey));

        if (!$client) {
            return response()->json([
                'error'   => true,
                'message' => 'Invalid API key'
            ], 401);
        }

        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');
        $originHost = $origin ? parse_url($origin, PHP_URL_HOST) : null;
        if (
            ! is_string($originHost)
            || ! UserApiDomain::query()
                ->where('user_id', $client->id)
                ->where('domain_hash', hash('sha256', strtolower(rtrim($originHost, '.'))))
                ->where('is_active', true)
                ->exists()
        ) {
            return response()->json([
                'error' => true,
                'message' => 'This API key is not authorized for the request domain',
            ], 403);
        }

        $request->setUserResolver(static fn (?string $guard = null): User => $client);
        $request->attributes->set('client', $client);

        return $next($request);
    }
}
