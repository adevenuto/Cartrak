<?php

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Global catalogue of serviceable items. A type carrying BOTH interval axes is
 * how "whichever comes first" is expressed; one axis alone schedules on that
 * axis only.
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $category
 * @property int|null $default_interval_months
 * @property int|null $default_interval_miles
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'key', 'name', 'category', 'default_interval_months',
    'default_interval_miles', 'sort_order',
])]
class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory;

    /**
     * @return HasMany<VehicleInterval, $this>
     */
    public function vehicleIntervals(): HasMany
    {
        return $this->hasMany(VehicleInterval::class);
    }

    /**
     * @return HasMany<LineItem, $this>
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class);
    }
}
