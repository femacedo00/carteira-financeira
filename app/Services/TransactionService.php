<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class TransactionService
{
    /**
     * Verify if it's within the permitted window for refund
     */
    public function isWithinRefundWindow(Transaction $transaction): bool
    {
        // Days allowed for refund
        $allowedDays = config('wallet.limits.night_deposit', 7);

        // Calcualtes the difference between the deposit date and the refund date
        $daysPassed = $transaction->created_at->diffInDays(now());

        // If allowed: true
        return $daysPassed <= $allowedDays;
    }

    /**
     * Gets a user transaction by token
     */
    public function getTransaction(User $user, string $token): Transaction
    {
        // Gets the original transaction
        return Transaction::where('token', $token)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('related_user_id', $user->id);
            })
            ->firstOr(function () {
                // If not found: error
                throw new Exception('Original transaction not found or invalid token.');
            });
    }

    /**
     * Verify if deposit has already been refunded.
     */
    public function isAlreadyRefunded(User $user, int $original_transaction_id): bool
    {
        return $user->transactions()
            ->where('original_transaction_id', $original_transaction_id)
            ->where('type', TransactionType::REFUND)
            ->exists();
    }

    /**
     * Verify if password is the same in the database.
     */
    public function isFinancialPasswordCorrect(User $user, string $financial_password): bool
    {
        if (! Hash::check($financial_password, $user->financial_password)) {
            // If failed, password is not the same
            return false;
        }

        return true;
    }

    /**
     * Verify if user has enough balance.
     */
    public function isEnoughBalance(float $balance, float $amount): bool
    {
        $isEnough = $balance >= $amount ? true : false;

        return $isEnough;
    }

    /**
     * Verify if both types is the same type.
     */
    public function isSameType(TransactionType $originType, TransactionType $currentType): bool
    {
        $isSame = $originType !== $currentType ? true : false;

        return $isSame;
    }

    /**
     * Verify if is nightTime.
     */
    public function isNightTime(): bool
    {
        // Get the exact time now, forcing the Brasília time zone.
        $now = now()->timezone('America/Sao_Paulo');

        $hour = $now->hour;

        // Is Night between 20 hours and 6 hours
        return $hour > 20 || $hour < 6;
    }

    /**
     * Returns the limit of total transaction from a specific Type and User.
     */
    public function limit(User $user, TransactionType $type): float
    {
        if ($type != TransactionType::DEPOSIT && $type != TransactionType::TRANSFER) {
            return 0;
        }

        if ($type == TransactionType::DEPOSIT) {
            return $this->isNightTime()
                ? $user->walletSetting->night_deposit_limit
                : $user->walletSetting->day_deposit_limit;
        }

        return $this->isNightTime()
            ? $user->walletSetting->night_transfer_limit
            : $user->walletSetting->day_transfer_limit;
    }

    /**
     * Returns the total amount of transactions from a specific Type and User.
     */
    public function depositsToday(User $user, TransactionType $type): float
    {
        return $user->transactions()
            ->where('type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');
    }

    /**
     * Calculates the remaining limit from a specific Type and User.
     */
    public function remainingLimit(User $user, TransactionType $type): float
    {
        $depositsToday = $this->depositsToday($user, $type);
        $limit = $this->limit($user, $type);

        return $limit - $depositsToday;
    }
}
