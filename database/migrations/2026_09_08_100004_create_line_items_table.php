<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();

            // Line items belong to a visit event: one shop visit, several jobs.
            // Saving the visit resets last_done_* on each referenced interval,
            // so one save can reset several gauges.
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('cost_cents')->nullable();
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
