<?php

namespace Database\Factories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = 'service-'.fake()->unique()->numberBetween(1, 1_000_000);

        return [
            'key' => $key,
            'name' => str($key)->headline()->value(),
            'category' => 'maintenance',
            'default_interval_months' => 12,
            'default_interval_miles' => 10000,
            'sort_order' => 0,
        ];
    }

    /**
     * Scheduled on mileage only — no time axis.
     */
    public function mileageOnly(): self
    {
        return $this->state(fn (): array => ['default_interval_months' => null]);
    }

    /**
     * Scheduled on time only — no mileage axis (registration, inspection).
     */
    public function timeOnly(): self
    {
        return $this->state(fn (): array => ['default_interval_miles' => null]);
    }
}
