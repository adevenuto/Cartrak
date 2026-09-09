<?php

namespace Database\Factories;

use App\Enums\IntervalSource;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleInterval>
 */
class VehicleIntervalFactory extends Factory
{
    protected $model = VehicleInterval::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'service_type_id' => ServiceType::factory(),
            'interval_months' => 12,
            'interval_miles' => 10000,
            'source' => IntervalSource::Default,
            'last_done_at' => null,
            'last_done_odometer' => null,
            'is_active' => true,
        ];
    }
}
