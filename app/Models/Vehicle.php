<?php

namespace App\Models;

use App\Observers\VehicleObserver;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $nickname
 * @property string|null $vin
 * @property int|null $year
 * @property string|null $make
 * @property string|null $model
 * @property string|null $trim
 * @property string|null $engine
 * @property string|null $color
 * @property array<string, mixed>|null $decoded_specs
 * @property int|null $last_odometer
 * @property Carbon|null $last_odometer_at
 * @property string|null $avg_miles_per_day
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy(VehicleObserver::class)]
#[Fillable([
    'nickname', 'vin', 'year', 'make', 'model', 'trim', 'engine', 'color',
    'decoded_specs', 'last_odometer', 'last_odometer_at', 'avg_miles_per_day',
])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decoded_specs' => 'array',
            'last_odometer_at' => 'date',
            'year' => 'integer',
            'last_odometer' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return HasMany<VehicleInterval, $this>
     */
    public function intervals(): HasMany
    {
        return $this->hasMany(VehicleInterval::class);
    }

    /**
     * @return HasMany<VehiclePhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('position');
    }

    /**
     * The card image: whichever photo sits at position 0.
     *
     * oldestOfMany builds a real HasOne over a MIN(position) subquery, so
     * eager-loading this on the garage index costs ONE extra query in total —
     * unlike an accessor (which would lazy-load per row) or photos->first()
     * (which would load all six photos per vehicle to use one).
     *
     * @return HasOne<VehiclePhoto, $this>
     */
    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(VehiclePhoto::class)->oldestOfMany('position');
    }

    /**
     * What to call this vehicle in the UI: the user's nickname if they gave one,
     * otherwise the year/make/model they entered, otherwise a bare fallback so
     * a card is never blank.
     */
    public function displayName(): string
    {
        if (filled($this->nickname)) {
            return $this->nickname;
        }

        $parts = array_filter([$this->year, $this->make, $this->model]);

        return $parts === [] ? 'Vehicle' : implode(' ', $parts);
    }
}
