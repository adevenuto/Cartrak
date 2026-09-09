<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // A #RRGGBB paint colour, so a vehicle is identifiable at a glance
            // in the garage. Nullable: colour is never required to add a car.
            $table->string('color', 7)->nullable()->after('engine');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
