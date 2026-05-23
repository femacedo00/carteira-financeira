<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Creates a new user and their associated wallet settings.
     */
    public function createUser(array $data): User
    {
        // DB::transaction guarantees that either everything will be saved or nothin will be saved in the database
        return DB::transaction(function () use ($data) {
            // Creating the User
            $user = User::Create($data);

            // Creating the wallet settings using config file
            $user->walletSetting()->create([
                'day_transfer_limit' => config('wallet.limits.day_transfer', 1000.00),
                'night_transfer_limit' => config('wallet.limits.night_transfer', 200.00),
                'day_deposit_limit' => config('wallet.limits.day_deposit', 5000.00),
                'night_deposit_limit' => config('wallet.limits.night_deposit', 1000.00),
            ]);

            return $user;
        });
    }

    /**
     * Update financial Passowrd
     */
    public function updateFinancialPassword(User $user, string $financial_password): void
    {
        $updated = $user->update([
            'financial_password' => $financial_password,
        ]);

        if (! $updated) {
            throw new Exception('Somenthing went wrong. Try Again');
        }
    }
}
