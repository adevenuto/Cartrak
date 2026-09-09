<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_intervals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();

            // Effective intervals: seeded from the service type's defaults, then
            // overridden per vehicle. Null on an axis means "not scheduled on it".
            $table->unsignedSmallInteger('interval_months')->nullable();
            $table->unsignedInteger('interval_miles')->nullable();

            $table->string('source')->default('default');

            $table->date('last_done_at')->nullable();
            $table->unsignedInteger('last_done_odometer')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vehicle_id', 'service_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_intervals');
    }
};
