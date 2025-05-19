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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plaid_item_id');
            $table->string('plaid_account_id');
            $table->text('plaid_access_token');
            $table->string('institution_name');
            $table->string('account_name');
            $table->string('account_type');
            $table->string('account_mask', 4);
            $table->string('dwolla_funding_source_url')->nullable();
            $table->timestamps();
            
            // Add unique index to prevent duplicate accounts
            $table->unique(['user_id', 'plaid_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
