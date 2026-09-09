<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nickname' => null,
            'vin' => null,
            'year' => fake()->numberBetween(2005, 2025),
            'make' => fake()->randomElement(['Toyota', 'Honda', 'Ford', 'Subaru']),
            'model' => fake()->randomElement(['RAV4', 'Civic', 'F-150', 'Outback']),
            'trim' => null,
            'engine' => null,
            'decoded_specs' => null,
            'last_odometer' => null,
            'last_odometer_at' => null,
            'avg_miles_per_day' => null,
        ];
    }

    /**
     * A vehicle that already has a known reading, without going through an event.
     */
    public function withOdometer(int $miles, ?string $on = null): self
    {
        return $this->state(fn (): array => [
            'last_odometer' => $miles,
            'last_odometer_at' => $on ?? now()->toDateString(),
        ]);
    }
}
