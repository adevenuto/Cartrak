<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_intervals', function (Blueprint $table) {
            // What we last told the user about this item, and when. Together
            // these are what stop a daily re-check from re-sending the same
            // reminder every morning — the thing that gets an app muted.
            //
            // Both reset when the service is logged, so the next cycle starts
            // clean.
            $table->string('last_reminded_status')->nullable()->after('is_active');
            $table->timestamp('last_reminded_at')->nullable()->after('last_reminded_status');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_intervals', function (Blueprint $table) {
            $table->dropColumn(['last_reminded_status', 'last_reminded_at']);
        });
    }
};
