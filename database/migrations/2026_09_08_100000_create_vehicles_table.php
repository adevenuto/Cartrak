<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('nickname')->nullable();
            $table->string('vin', 17)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('trim')->nullable();
            $table->string('engine')->nullable();

            // Cached VIN decode from NHTSA vPIC. Populated in Phase 3; the app
            // must stay fully functional while it is null.
            $table->json('decoded_specs')->nullable();

            // Mileage estimate inputs. Phase 1 writes these on every event that
            // carries an odometer; Phase 2 projects forward from them.
            $table->unsignedInteger('last_odometer')->nullable();
            $table->date('last_odometer_at')->nullable();
            $table->decimal('avg_miles_per_day', 8, 2)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
