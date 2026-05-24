<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRefundRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Services\DepositService;
use App\Services\TransferService;
use Exception;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private DepositService $depositService,
        private TransferService $transferService
    ) {}

    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            // Get the amount to transfer
            $transfer = $request->validated();

            // Get the user that request the transfer
            $user = $request->user();

            // Transfer the amount
            $deposit = $this->transferService->executeTransfer($user, $transfer);

            return Response()->json([
                'message' => 'Transfer processed successfully',
                'data' => $deposit,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Operation failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function transferRefund(DepositRefundRequest $request): JsonResponse
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
