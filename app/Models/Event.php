<?php

namespace App\Models;

use App\Enums\EventType;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The one spine. Services, fuel-ups, expenses and bare mileage check-ins are all
 * rows in this table, each carrying an odometer reading and a date.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property EventType $type
 * @property int $odometer
 * @property Carbon $occurred_on
 * @property int|null $cost_cents
 * @property string|null $notes
 * @property string|null $location
 * @property string|null $photo_path
 * @property string|null $gallons
 * @property bool|null $full_tank
 * @property string|null $mpg
 * @property string|null $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'type', 'odometer', 'occurred_on', 'cost_cents', 'notes', 'location',
    'photo_path', 'gallons', 'full_tank', 'mpg', 'category',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'occurred_on' => 'date',
            'full_tank' => 'boolean',
            'odometer' => 'integer',
            'cost_cents' => 'integer',
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
     * @return HasMany<LineItem, $this>
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class);
    }

    /**
     * Newest first, tie-broken by odometer then id so two events logged on the
     * same date still have a stable, meaningful order.
     *
     * @param  Builder<Event>  $query
     */
    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByDesc('occurred_on')
            ->orderByDesc('odometer')
            ->orderByDesc('id');
    }
}
