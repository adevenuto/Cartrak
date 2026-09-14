<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // EPA sticker figures, cached so the real-vs-sticker benchmark works
            // with fueleconomy.gov unreachable. Nullable throughout: plenty of
            // vehicles simply are not in the database.
            $table->unsignedSmallInteger('epa_mpg_city')->nullable()->after('decoded_specs');
            $table->unsignedSmallInteger('epa_mpg_highway')->nullable()->after('epa_mpg_city');
            $table->unsignedSmallInteger('epa_mpg_combined')->nullable()->after('epa_mpg_highway');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['epa_mpg_city', 'epa_mpg_highway', 'epa_mpg_combined']);
        });
    }
};
