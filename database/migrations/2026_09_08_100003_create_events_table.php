<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // The spine: every event carries an odometer reading and a date.
            $table->string('type');
            $table->unsignedInteger('odometer');
            $table->date('occurred_on');

            // Money is stored as integer cents everywhere. For a fuel event this
            // is the receipt total, which is why fuel needs no separate column.
            $table->unsignedInteger('cost_cents')->nullable();

            $table->text('notes')->nullable();
            $table->string('location')->nullable();

            // Column exists so the schema matches the brief's data model; upload
            // handling is deliberately deferred to a later phase.
            $table->string('photo_path')->nullable();

            // Fuel-only. mpg is derived on save from the odometer delta since the
            // previous full-tank fuel event, so it is null on the first fill-up
            // and on partial fills.
            $table->decimal('gallons', 6, 3)->nullable();
            $table->boolean('full_tank')->nullable();
            $table->decimal('mpg', 6, 2)->nullable();

            // Expense-only.
            $table->string('category')->nullable();

            $table->timestamps();

            $table->index(['vehicle_id', 'occurred_on']);
            $table->index(['vehicle_id', 'odometer']);
            $table->index(['vehicle_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
