<?php

namespace App\Models;

use App\Enums\IntervalSource;
use Database\Factories\VehicleIntervalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A vehicle's schedule row for one service type: the effective intervals, where
 * they came from, and when the job was last done.
 *
 * Phase 1 creates these and keeps last_done_* current. Computing progress and
 * the binding constraint across both axes is Phase 2.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property int $service_type_id
 * @property int|null $interval_months
 * @property int|null $interval_miles
 * @property IntervalSource $source
 * @property Carbon|null $last_done_at
 * @property int|null $last_done_odometer
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'service_type_id', 'interval_months', 'interval_miles', 'source',
    'last_done_at', 'last_done_odometer', 'is_active',
])]
class VehicleInterval extends Model
{
    /** @use HasFactory<VehicleIntervalFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => IntervalSource::class,
            'last_done_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }
}
