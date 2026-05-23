<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Authenticates the user
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            $authData = $this->authService->authenticate($credentials);

            return response()->json(
                $authData,
                200
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Removes the current session
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(
            [],
            204
        );
    }
}
