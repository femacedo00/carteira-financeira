<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepositService extends TransactionService
{
    /**
     * Create a new class instance.
     */
    public function executeDeposit(User $user, float $amount): Transaction
    {
        $remainingLimit = $this->remainingLimit($user, TransactionType::DEPOSIT);
        if ($remainingLimit < $amount) {
            throw new Exception('The amount exceeds your daily deposit limit. Remaining limit for today: R$'.number_format($remainingLimit, 2, ',', '.'));
        }

        // If it passes validation, it executes the deposit transaction
        return DB::transaction(function () use ($user, $amount) {

            $transaction = Transaction::create([
                'token' => Str::uuid(),
                'user_id' => $user->id,
                'type' => TransactionType::DEPOSIT,
                'amount' => $amount,
            ]);

            // Update balance directly with the bank to avoid race conditions.
            $user->increment('balance', $amount);

            return $transaction;
        });
    }
}
