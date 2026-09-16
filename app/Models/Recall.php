<?php

namespace App\Models;

use Database\Factories\RecallFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A safety recall NHTSA has published against a vehicle, cached locally.
 *
 * Cached rather than fetched on demand: the brief is explicit that external
 * APIs are enrichment and never a hard dependency, so the vehicle page must
 * render its recalls with NHTSA unreachable.
 *
 * @property int $id
 * @property int $vehicle_id
 * @property string $campaign_number
 * @property string|null $component
 * @property string|null $summary
 * @property string|null $remedy
 * @property string|null $consequence
 * @property Carbon|null $reported_on
 * @property Carbon|null $acknowledged_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'campaign_number', 'component', 'summary', 'remedy', 'consequence',
    'reported_on', 'acknowledged_at',
])]
class Recall extends Model
{
    /** @use HasFactory<RecallFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_on' => 'date',
            'acknowledged_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isOpen(): bool
    {
        return $this->acknowledged_at === null;
    }
}
