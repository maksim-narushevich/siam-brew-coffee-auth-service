<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login']),
        ];
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login user",
     *     description="Login user via email and password",
     *     operationId="authLogin",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"email": "test@test.com", "password": "password"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     *
     * @throws AuthenticationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if ($user === null || ! Hash::check($validated['password'], $user->password)) {
            throw new AuthenticationException;
        }

        if (! $token = Auth::claims(['userUuid' => $user->uuid])->attempt($validated)) {
            return response()->json(['error' => 'Unauthorized'], ResponseStatus::UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *     path="/auth/me",
     *     summary="Get auth user data",
     *     description="Get authenticated user data",
     *     operationId="authMe",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user(),
            'payload' => Auth::payload(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/verify",
     *     summary="Verify token of authenticated user",
     *     description="Verify token of authenticated user",
     *     operationId="verify",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function verify(): JsonResponse
    {
        return response()->json(['status' => Auth::check()]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout auth user",
     *     description="Logout auth user",
     *     operationId="authLogout",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        Auth::logout();

        return response()->json(
            [
                'status' => ResponseStatus::HTTP_OK,
                'message' => 'Successfully logged out.',
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh auth user JWT token",
     *     description="Refresh auth user JWT token",
     *     operationId="authRefresh",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::refresh());
    }

    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }
}
