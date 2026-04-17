<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Sanctum-based token authentication for first- and third-party clients.
 *
 * Endpoints:
 *  - POST /api/v1/auth/token   issue a personal access token (rate-limited)
 *  - GET  /api/v1/auth/me      return the authenticated user
 *  - POST /api/v1/auth/logout  revoke the current access token
 */
class AuthController extends Controller
{
    /**
     * Exchange email + password for a 30-day personal access token.
     *
     * Request body:
     *  - email        (string, required, RFC-5321)
     *  - password     (string, required)
     *  - device_name  (string, required, <=80 chars) — stored on the token
     *
     * @throws ValidationException when credentials do not match
     */
    public function token(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:80'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('Invalid credentials.')],
            ]);
        }

        $token = $user->createToken($data['device_name'], ['*'], now()->addDays(30));

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => optional($token->accessToken->expires_at)->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
        ]);
    }

    /**
     * Return a minimal representation of the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
        ]);
    }

    /**
     * Revoke the access token presented by the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['status' => 'ok']);
    }
}
