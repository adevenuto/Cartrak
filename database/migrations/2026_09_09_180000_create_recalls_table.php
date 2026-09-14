<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // NHTSA's own identifier for the campaign. Unique per vehicle so a
            // weekly re-check upserts rather than duplicating, and so we can
            // tell a genuinely NEW campaign from one we have already seen.
            $table->string('campaign_number');
            $table->string('component')->nullable();
            $table->text('summary')->nullable();
            $table->text('remedy')->nullable();
            $table->text('consequence')->nullable();
            $table->date('reported_on')->nullable();

            // Set when the owner marks it handled — NHTSA cannot tell us this.
            $table->timestamp('acknowledged_at')->nullable();

            $table->timestamps();

            $table->unique(['vehicle_id', 'campaign_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recalls');
    }
};
