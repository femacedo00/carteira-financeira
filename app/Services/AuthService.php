<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Ckecks the credentials and creates a token
     */
    public function authenticate(array $credentials): array
    {
        $user = User::where('cpf_cnpj', $credentials['cpf_cnpj'])->first();

        // Try logging with the credentials
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // If the attempt failed, something is wrong with the credentials
            throw ValidationException::withMessages([
                'message' => 'Credentials invalid. Check your CPF/CNPJ and Password',
            ]);
        }

        // Deletes all old tokens to ensure that only one session is active
        $user->tokens()->delete();

        // Creates a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ];
    }
}
