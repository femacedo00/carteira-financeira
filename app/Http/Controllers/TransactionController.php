<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRefundRequest;
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
            // Get the amount to deposit
            $deposit = $request->validated();

            // Get the user that request the deposit
            $user = $request->user();

            // Deposit the amount
            $deposit = $this->depositService->executeDeposit($user, $deposit);

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

    public function depositRefund(DepositRefundRequest $request): JsonResponse
    {
        try {
            // Get the token of deposit
            $refund = $request->validated();

            // Get the user that request the refund
            $user = $request->user();

            // Refound the deposit
            $deposit = $this->depositService->executeRefundDeposit($user, $refund);

            return Response()->json([
                'message' => 'Deposit refund processed successfully',
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
