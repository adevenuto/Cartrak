<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'type' => EventType::Odometer,
            'odometer' => fake()->numberBetween(1000, 150000),
            'occurred_on' => now()->toDateString(),
            'cost_cents' => null,
            'notes' => null,
            'location' => null,
            'photo_path' => null,
            'gallons' => null,
            'full_tank' => null,
            'mpg' => null,
            'category' => null,
        ];
    }

    public function visit(?int $costCents = 8995): self
    {
        return $this->state(fn (): array => [
            'type' => EventType::Visit,
            'cost_cents' => $costCents,
            'location' => 'Corner Auto',
        ]);
    }

    public function fuel(float $gallons = 12.5, int $costCents = 4499, bool $fullTank = true): self
    {
        return $this->state(fn (): array => [
            'type' => EventType::Fuel,
            'gallons' => $gallons,
            'cost_cents' => $costCents,
            'full_tank' => $fullTank,
        ]);
    }

    public function expense(string $category = 'Insurance', int $costCents = 12000): self
    {
        return $this->state(fn (): array => [
            'type' => EventType::Expense,
            'category' => $category,
            'cost_cents' => $costCents,
        ]);
    }

    public function on(string $date, int $odometer): self
    {
        return $this->state(fn (): array => [
            'occurred_on' => $date,
            'odometer' => $odometer,
        ]);
    }
}
