<?php

namespace Database\Factories;

use App\Models\Recall;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recall>
 */
class RecallFactory extends Factory
{
    protected $model = Recall::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'campaign_number' => strtoupper(fake()->bothify('##V###000')),
            'component' => 'FUEL SYSTEM, GASOLINE:DELIVERY:FUEL PUMP',
            'summary' => 'The fuel pump may fail, causing the engine to stall.',
            'remedy' => 'Dealers will replace the fuel pump assembly, free of charge.',
            'consequence' => 'An engine stall while driving increases the risk of a crash.',
            'reported_on' => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
            'acknowledged_at' => null,
        ];
    }

    public function acknowledged(): self
    {
        return $this->state(fn (): array => ['acknowledged_at' => now()]);
    }
}
