<?php

namespace App\Http\Controllers;

use App\HTTP\Requests\StoreUserRequest;
use App\Http\Requests\UpdateFinancialPasswordRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        // Validates using the StoreUserRequest class
        $validated = $request->validated();

        // Creates User using the UserService class
        $user = $this->userService->createUser($validated);

        // Returns a succssefull HTTP Response
        return response()->json([
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function updateFinancialPassoword(UpdateFinancialPasswordRequest $request): JsonResponse
    {
        try {
            // Validates using the UpdateFinancialPasswordRequest class
            $financial_password = $request->validated('financial_password');

            // Get the user that request the update
            $user = $request->user();

            // Creates financial password using the UserService class
            $this->userService->updateFinancialPassword($user, $financial_password);

            // Returns a succssefull HTTP Response
            return response()->json([
                'message' => 'Password created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Operation failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
