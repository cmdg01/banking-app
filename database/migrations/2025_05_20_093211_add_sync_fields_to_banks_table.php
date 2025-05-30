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
        Schema::table('banks', function (Blueprint $table) {
            $table->string('plaid_cursor')->nullable()->after('dwolla_funding_source_url');
            $table->timestamp('last_synced_at')->nullable()->after('plaid_cursor');
            $table->decimal('balance_available', 10, 2)->nullable()->after('last_synced_at');
            $table->decimal('balance_current', 10, 2)->nullable()->after('balance_available');
            $table->decimal('balance_limit', 10, 2)->nullable()->after('balance_current');
            $table->string('balance_currency', 3)->default('USD')->after('balance_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn([
                'plaid_cursor',
                'last_synced_at',
                'balance_available',
                'balance_current',
                'balance_limit',
                'balance_currency',
            ]);
        });
    }
};