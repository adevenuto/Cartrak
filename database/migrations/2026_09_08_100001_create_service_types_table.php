<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->nullable();

            // "Whichever comes first" is expressed by a type carrying BOTH; a
            // type with only one axis is scheduled on that axis alone.
            $table->unsignedSmallInteger('default_interval_months')->nullable();
            $table->unsignedInteger('default_interval_miles')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
