<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Token that will be used for a possible refund
            $table->uuid('token')->unique();

            // Transaction owner
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Transaction receiver
            $table->foreignId('related_user_id')->nullable()->constrained('users');

            // deposit, transfer, refund
            $table->string('type');

            // Transaction amount
            $table->decimal('amount', 10, 2);

            // Self-relationship to tie a refund to the original transaction.
            $table->foreignId('original_transaction_id')->nullable()->constrained('transactions');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
