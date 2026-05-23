<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
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
}
