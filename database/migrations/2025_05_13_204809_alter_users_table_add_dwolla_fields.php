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
        Schema::table('users', function (Blueprint $table) {
            // Personal info for Dwolla
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->after('id')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->after('first_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'address_line_1')) {
                $table->string('address_line_1')->after('email_verified_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'address_line_2')) {  // Changed from address2 → address_line_2
                $table->string('address_line_2')->nullable()->after('address_line_1');  // Renamed
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->after('address_line_2')->nullable();  // Update order
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state', 2)->after('city')->nullable(); // e.g., "CA"
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code')->after('state')->nullable();
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->after('postal_code')->nullable();
            }
            if (!Schema::hasColumn('users', 'ssn')) {
                $table->string('ssn')->after('date_of_birth')->nullable(); // Consider encryption
            }

            // Dwolla integration fields
            if (!Schema::hasColumn('users', 'dwolla_customer_id')) {
                $table->string('dwolla_customer_id')->nullable()->index()->after('ssn');
            }
            if (!Schema::hasColumn('users', 'dwolla_customer_url')) {
                $table->string('dwolla_customer_url')->nullable()->after('dwolla_customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'address_line_1',   // Updated from address1
                'address_line_2',   // Updated from address2
                'city',
                'state',
                'postal_code',
                'date_of_birth',
                'ssn',
                'dwolla_customer_id',
                'dwolla_customer_url'
            ]);
        });
    }
};