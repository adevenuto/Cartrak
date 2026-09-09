<?php

namespace App\Models;

use Database\Factories\LineItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One job on a shop visit. A visit with several line items resets several
 * vehicle intervals in a single save.
 *
 * @property int $id
 * @property int $event_id
 * @property int $service_type_id
 * @property int|null $cost_cents
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['service_type_id', 'cost_cents', 'notes'])]
class LineItem extends Model
{
    /** @use HasFactory<LineItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }
}
