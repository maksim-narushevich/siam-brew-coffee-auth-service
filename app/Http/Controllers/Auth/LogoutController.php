<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ResponseStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

final class LogoutController extends Controller
{
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
    public function __invoke(): JsonResponse
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
}
