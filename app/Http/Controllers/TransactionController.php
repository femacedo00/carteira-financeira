<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Services\DepositService;
use Exception;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private DepositService $depositService
    ) {}

    public function deposit(DepositRequest $request): JsonResponse
    {
        try {
            $amount = $request->validated('amount');

            // Get the user that request the deposit
            $user = $request->user();

            $deposit = $this->depositService->executeDeposit($user, $amount);

            return Response()->json([
                'message' => 'Deposit processed successfully',
                'data' => $deposit,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Operation failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
