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
        Schema::table('transactions', function (Blueprint $table) {
            // Add fields for anomaly detection and review
            $table->boolean('is_reviewed')->default(false)->after('channel');
            $table->boolean('is_legitimate')->nullable()->after('is_reviewed');
            $table->text('review_feedback')->nullable()->after('is_legitimate');
            $table->timestamp('reviewed_at')->nullable()->after('review_feedback');
            $table->json('anomaly_data')->nullable()->after('reviewed_at');
            $table->text('anomaly_explanation')->nullable()->after('anomaly_data');
            $table->boolean('is_anomaly')->default(false)->after('anomaly_explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Remove fields added for anomaly detection
            $table->dropColumn([
                'is_reviewed',
                'is_legitimate',
                'review_feedback',
                'reviewed_at',
                'anomaly_data',
                'anomaly_explanation',
                'is_anomaly',
            ]);
        });
    }
};
