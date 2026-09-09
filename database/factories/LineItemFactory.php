<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\LineItem;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LineItem>
 */
class LineItemFactory extends Factory
{
    protected $model = LineItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->visit(),
            'service_type_id' => ServiceType::factory(),
            'cost_cents' => 4500,
            'notes' => null,
        ];
    }
}
