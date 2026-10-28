<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UserApiTokenController extends Controller
{
    public function create(User $user): JsonResponse
    {
        if (! in_array($user->role, [1, 2], true)) {
            return response()->json([
                'message' => 'API access is only available for hospital and doctor accounts.',
            ], 409);
        }

        if ($user->api_token) {
            return response()->json([
                'message' => 'An API token already exists. View it instead of creating another token.',
            ], 409);
        }

        $token = Str::random(16);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'message' => 'API token created. You can view this token again from the admin panel.',
            'token' => $token,
        ], 201)->header('Cache-Control', 'no-store, private');
    }

    public function view(User $user): JsonResponse
    {
        if (! in_array($user->role, [1, 2], true)) {
            return response()->json([
                'message' => 'API access is only available for hospital and doctor accounts.',
            ], 409);
        }

        if (! $user->api_token) {
            return response()->json([
                'message' => 'No viewable API token exists for this account.',
            ], 404);
        }

        return response()->json([
            'message' => 'Existing API token. It has not been changed.',
            'token' => $user->api_token,
        ])->header('Cache-Control', 'no-store, private');
    }
}
