<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\User;

class TransactionService
{
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
                : $user->walletSetting->daily_deposit_limit;
        }

        return $this->isNightTime()
            ? $user->walletSetting->night_transfer_limit
            : $user->walletSetting->daily_transfer_limit;
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
