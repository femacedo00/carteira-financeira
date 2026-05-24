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
     * Gets the user infos to execute transfer or refund
     */
    public function getUser(string $user_info, string $column = 'cpf_cnpj'): User
    {
        return User::where($column, $user_info)
            ->firstOr(function () {
                // If not found: error
                throw new Exception('User not found');
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
        $addressee = $this->getUser($addressee_document);

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

    /**
     * Verify if the addresse is the one who wants to refund
     */
    public function addresseWantsRefund(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->related_user_id;
    }

    /**
     * Method to refund a transfer in the DataBase
     */
    public function executeRefundTransfer(User $user, array $refund): TransactionResource
    {
        $financial_password = $refund['financial_password'];

        // Checks if the financial password is correct
        if (! $this->isFinancialPasswordCorrect($user, $financial_password)) {
            // If false, something is wrong with the credentials
            throw new Exception('The financial password is incorrect');
        }

        $token = $refund['token'];

        // Gets the original transfer to refund
        $originalTransfer = $this->getTransaction($user, $token);

        $original_transaction_id = $originalTransfer->id;
        $amount = $originalTransfer->amount;

        // Checks if token belongs to a transfer
        if ($this->isSameType($originalTransfer->type, TransactionType::TRANSFER)) {
            throw new Exception('This transaction is not a transfer');
        }

        // Checks if transfer has already been rufunded.
        if ($this->isAlreadyRefunded($user, $original_transaction_id)) {
            // If true, there is no need to do another refund
            throw new Exception('Already refunded');
        }

        // Checks if it's within the permitted window for refund
        if (! $this->isWithinRefundWindow($originalTransfer)) {
            // If it's not, returns error
            throw new Exception('The refund period has expired');
        }

        // Checks if the addresse is the one who wants to refund
        if ($this->addresseWantsRefund($user, $originalTransfer)) {
            // If true, search for the user who transferred it
            $addressee = $user;
            $user = $this->getUser($originalTransfer->user_id, 'id');
        } else {
            // If false, search for the addressee who received it
            $addressee = $this->getUser($originalTransfer->related_user_id, 'id');
        }

        // If it passes validation, it executes the refund transaction
        return DB::transaction(function () use ($user, $addressee, $original_transaction_id, $amount) {

            $transaction = Transaction::create([
                'token' => Str::uuid(),
                'user_id' => $user->id,
                'type' => TransactionType::REFUND,
                'amount' => $amount,
                'related_user_id' => $addressee->id,
                'original_transaction_id' => $original_transaction_id,
            ]);

            // Update balance directly with the bank to avoid race conditions.
            $user->increment('balance', $amount);
            $addressee->decrement('balance', $amount);

            return new TransactionResource($transaction);
        });
    }
}
