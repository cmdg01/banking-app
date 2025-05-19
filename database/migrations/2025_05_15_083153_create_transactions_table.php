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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_id')->constrained()->onDelete('cascade');
            $table->string('plaid_transaction_id')->index();
            $table->string('plaid_account_id')->index();
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->json('payment_meta')->nullable();
            $table->string('channel')->default('plaid');
            $table->timestamps();
            
            // Add unique index to prevent duplicate transactions
            $table->unique(['user_id', 'plaid_transaction_id']);
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
