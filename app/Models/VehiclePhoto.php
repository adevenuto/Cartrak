<?php

namespace App\Models;

use App\Observers\VehiclePhotoObserver;
use Database\Factories\VehiclePhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One stored photo of a vehicle.
 *
 * Position 0 is the card image, everywhere the vehicle is represented. That is a
 * derivation rather than a stored flag, so it cannot disagree with the ordering.
 *
 * Routed by ULID rather than id: an <img src> carrying a sequential id is
 * enumerable, and the 403/404 split would then be a clean oracle for how many
 * photos exist. The ULID is also the filename stem, so a row and its files can
 * be reconciled in either direction.
 *
 * @property int $id
 * @property string $ulid
 * @property int $vehicle_id
 * @property string $path
 * @property string $thumbnail_path
 * @property int $position
 * @property int $width
 * @property int $height
 * @property int $bytes
 * @property string|null $placeholder_color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[RouteKey('ulid')]
#[ObservedBy(VehiclePhotoObserver::class)]
#[Fillable([
    'ulid', 'path', 'thumbnail_path', 'position', 'width', 'height', 'bytes',
    'placeholder_color',
])]
class VehiclePhoto extends Model
{
    /** @use HasFactory<VehiclePhotoFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * ULIDs are generated for the `ulid` column only — the primary key stays a
     * plain auto-incrementing integer, because the form payload references
     * photos by id and integers keep that small.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'bytes' => 'integer',
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
     * The card image is simply the first one.
     */
    public function isPrimary(): bool
    {
        return $this->position === 0;
    }
}
