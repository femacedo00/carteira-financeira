<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepositService extends TransactionService
{
    /**
     * Method to insert a deposit in the DataBase
     */
    public function executeDeposit(User $user, array $deposit): TransactionResource
    {
        $amount = $deposit['amount'];
        $depositType = TransactionType::DEPOSIT;

        // Gets the limit of user
        $remainingLimit = $this->remainingLimit($user, $depositType);
        if ($remainingLimit < $amount) {
            // If reached the limit of it, returns error
            throw new Exception('The amount exceeds your deposit limit. Remaining limit for today: R$'.number_format($remainingLimit, 2, ',', '.'));
        }

        // If it passes validation, it executes the deposit transaction
        return DB::transaction(function () use ($user, $amount, $depositType) {

            $transaction = Transaction::create([
                'token' => Str::uuid(),
                'user_id' => $user->id,
                'type' => $depositType,
                'amount' => $amount,
            ]);

            // Update balance directly with the bank to avoid race conditions.
            $user->increment('balance', $amount);

            return new TransactionResource($transaction);
        });
    }

    /**
     * Method to refund a deposit in the DataBase
     */
    public function executeRefundDeposit(User $user, array $refund): TransactionResource
    {
        $financial_password = $refund['password'];

        // Checks if the financial password is correct
        if (! $this->isFinancialPasswordCorrect($user, $financial_password)) {
            // If false, something is wrong with the credentials
            throw new Exception('The financial password is incorrect');
        }

        $token = $refund['token'];

        // Gets the original deposit to refund
        $originalDeposit = $this->getTransaction($user, $token);

        $original_transaction_id = $originalDeposit->id;
        $amount = $originalDeposit->amount;

        // Checks if token belongs to a deposit
        if ($this->isSameType($originalDeposit->type, TransactionType::DEPOSIT)) {
            throw new Exception('This transaction is not a deposit');
        }

        // Checks if deposit has already been rufunded.
        if ($this->isAlreadyRefunded($user, $original_transaction_id)) {
            // If true, there is no need to do another refund
            throw new Exception('Already refunded');
        }

        // Checks if it's within the permitted window for refund
        if (! $this->isWithinRefundWindow($originalDeposit)) {
            // If it's not, returns error
            throw new Exception('The refund period has expired');
        }

        // If it passes validation, it executes the refund transaction
        return DB::transaction(function () use ($user, $original_transaction_id, $amount) {

            $transaction = Transaction::create([
                'token' => Str::uuid(),
                'user_id' => $user->id,
                'type' => TransactionType::REFUND,
                'amount' => $amount,
                'original_transaction_id' => $original_transaction_id,
            ]);

            // Update balance directly with the bank to avoid race conditions.
            $user->decrement('balance', $amount);

            return new TransactionResource($transaction);
        });
    }
}
