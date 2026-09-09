<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();

            // Route key and filename stem. Unguessable, so a leaked photo URL is
            // not a neighbour of anyone else's, and path <-> row stays derivable
            // in both directions for orphan sweeps.
            $table->ulid('ulid')->unique();

            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->string('path');
            $table->string('thumbnail_path');

            // Position 0 IS the card image. Deliberately no is_primary boolean:
            // two columns for one fact means two ways to disagree, and deleting
            // the primary would need a separate promotion step. As a derivation
            // it cannot desynchronise.
            $table->unsignedTinyInteger('position')->default(0);

            // Dimensions of the STORED image (always <= 1600, so smallint is
            // plenty). Feeds width/height on the <img> so cards never shift.
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedInteger('bytes');

            // Dominant colour of the stored image, painted as the tile
            // background so the photo resolves in rather than popping from
            // grey. Covers the loading state and the error state for free.
            $table->string('placeholder_color', 7)->nullable();

            $table->timestamps();

            // Deliberately NOT unique: renumbering after a delete passes through
            // transiently duplicated positions, and MySQL constraints are not
            // deferrable. Uniqueness comes from always renumbering survivors
            // 0..n-1 in one deterministic pass.
            $table->index(['vehicle_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
    }
};
