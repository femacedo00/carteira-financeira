<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService extends TransactionService
{
    /**
     * Verify if the user is also the addressee
     */
    public function isUserTheAddressee(User $user, User $addressee): bool
    {
        return $user->id === $addressee->id;
    }

    /**
     * Gets the addressee infos to execute transfer
     */
    public function getAddressee(string $addressee_document): User
    {
        return User::where('cpf_cnpj', $addressee_document)
            ->firstOr(function () {
                // If not found: error
                throw new Exception('Adressee not found');
            });
    }

    /**
     * Method to insert a transfer in the DataBase
     */
    public function executeTransfer(User $user, array $transfer): TransactionResource
    {
        $financial_password = $transfer['financial_password'];

        // Checks if the financial password is correct
        if (! $this->isFinancialPasswordCorrect($user, $financial_password)) {
            // If false, something is wrong with the credentials
            throw new Exception('The financial password is incorrect');
        }

        $amount = $transfer['amount'];
        $transferType = TransactionType::TRANSFER;

        // Gets the limit of user
        $remainingLimit = $this->remainingLimit($user, $transferType);
        if ($remainingLimit < $amount) {
            // If reached the limit of it, returns error
            throw new Exception('The amount exceeds your daily transfer limit. Remaining limit for today: R$'.number_format($remainingLimit, 2, ',', '.'));
        }

        // Checks if user has enough balance
        if (! $this->isEnoughBalance($user->balance, $amount)) {
            throw new Exception('Insufficient balance for transfer');
        }

        $addressee_document = $transfer['addressee_document'];

        // Get and check if addressee exist
        $addressee = $this->getAddressee($addressee_document);

        // Checks if user is the addressee
        if ($this->isUserTheAddressee($user, $addressee)) {
            throw new Exception('You cannot transfer money to yourself.');
        }

        // If it passes validation, it executes the transfer transaction
        return DB::transaction(function () use ($user, $amount, $transferType, $addressee) {

            $transaction = Transaction::create([
                'token' => Str::uuid(),
                'user_id' => $user->id,
                'type' => $transferType,
                'amount' => $amount,
                'related_user_id' => $addressee->id,
            ]);

            // Update balance directly with the bank to avoid race conditions.
            $user->decrement('balance', $amount);
            $addressee->increment('balance', $amount);

            return new TransactionResource($transaction);
        });
    }
}
