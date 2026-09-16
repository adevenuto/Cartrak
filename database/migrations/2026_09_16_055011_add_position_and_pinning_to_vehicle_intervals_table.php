<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Design doc §6 needs two things the schema could not express: a FIXED display
 * order, and a pinned subset shown as large dials.
 *
 * Both columns land together rather than one now and one later, because they
 * are two halves of the same feature and a second migration over the same table
 * buys nothing.
 *
 * Until now the order was insertion order, which VehicleGauges::toArray() then
 * re-sorted by urgency — directly against §6, which forbids reordering overdue
 * items to the front. A gauge that moves under the pointer as its status
 * changes is worse than one that stays put.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_intervals', function (Blueprint $table): void {
            $table->unsignedSmallInteger('position')->default(0)->after('is_active');
            $table->boolean('is_pinned')->default(false)->after('position');
            $table->index(['vehicle_id', 'position']);
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('vehicle_intervals', function (Blueprint $table): void {
            $table->dropIndex(['vehicle_id', 'position']);
            $table->dropColumn(['position', 'is_pinned']);
        });
    }

    /**
     * Seed the order from the service catalogue, then pin the first three
     * active intervals per vehicle so every existing vehicle opens with a full
     * gauge wall rather than an empty one.
     */
    private function backfill(): void
    {
        $rows = DB::table('vehicle_intervals')
            ->join('service_types', 'service_types.id', '=', 'vehicle_intervals.service_type_id')
            ->orderBy('vehicle_intervals.vehicle_id')
            ->orderBy('service_types.sort_order')
            ->orderBy('vehicle_intervals.id')
            ->get([
                'vehicle_intervals.id',
                'vehicle_intervals.vehicle_id',
                'vehicle_intervals.is_active',
            ]);

        $position = [];
        $pinned = [];

        foreach ($rows as $row) {
            $vehicleId = $row->vehicle_id;
            $next = $position[$vehicleId] ?? 0;
            $position[$vehicleId] = $next + 1;

            $pinnedSoFar = $pinned[$vehicleId] ?? 0;
            $shouldPin = $row->is_active && $pinnedSoFar < 3;

            if ($shouldPin) {
                $pinned[$vehicleId] = $pinnedSoFar + 1;
            }

            DB::table('vehicle_intervals')
                ->where('id', $row->id)
                ->update(['position' => $next, 'is_pinned' => $shouldPin]);
        }
    }
};
