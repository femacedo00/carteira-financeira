<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/user', [UserController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::patch('financial-password', [UserController::class, 'updateFinancialPassoword']);

    Route::post('deposit', [TransactionController::class, 'deposit']);
    Route::post('deposit-refund', [TransactionController::class, 'depositRefund']);
});
