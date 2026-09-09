<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

/**
 * The global service catalogue.
 *
 * Intervals here are conservative, widely-applicable defaults — deliberately not
 * OEM-accurate, since the brief makes default templates plus user override the
 * spine and treats OEM schedules (CarMD, Phase 6) as an upgrade. A type with
 * both axes is due on whichever arrives first; a type with one axis is
 * scheduled on that axis alone.
 */
class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::catalog() as $index => $type) {
            ServiceType::updateOrCreate(
                ['key' => $type['key']],
                [...$type, 'sort_order' => $index],
            );
        }
    }

    /**
     * @return array<int, array{key: string, name: string, category: string, default_interval_months: int|null, default_interval_miles: int|null}>
     */
    public static function catalog(): array
    {
        return [
            // key, name, category, months, miles
            ['key' => 'oil-change', 'name' => 'Oil & Filter Change', 'category' => 'engine', 'default_interval_months' => 6, 'default_interval_miles' => 5000],
            ['key' => 'tire-rotation', 'name' => 'Tire Rotation', 'category' => 'tires', 'default_interval_months' => 6, 'default_interval_miles' => 6000],
            ['key' => 'engine-air-filter', 'name' => 'Engine Air Filter', 'category' => 'engine', 'default_interval_months' => 24, 'default_interval_miles' => 20000],
            ['key' => 'cabin-air-filter', 'name' => 'Cabin Air Filter', 'category' => 'interior', 'default_interval_months' => 12, 'default_interval_miles' => 15000],
            ['key' => 'brake-pads', 'name' => 'Brake Pads', 'category' => 'brakes', 'default_interval_months' => null, 'default_interval_miles' => 40000],
            ['key' => 'brake-fluid', 'name' => 'Brake Fluid', 'category' => 'brakes', 'default_interval_months' => 36, 'default_interval_miles' => 45000],
            ['key' => 'tire-replacement', 'name' => 'Tire Replacement', 'category' => 'tires', 'default_interval_months' => null, 'default_interval_miles' => 50000],
            ['key' => 'wheel-alignment', 'name' => 'Wheel Alignment', 'category' => 'tires', 'default_interval_months' => 24, 'default_interval_miles' => 30000],
            ['key' => 'battery', 'name' => 'Battery', 'category' => 'electrical', 'default_interval_months' => 48, 'default_interval_miles' => null],
            ['key' => 'wiper-blades', 'name' => 'Wiper Blades', 'category' => 'exterior', 'default_interval_months' => 12, 'default_interval_miles' => null],
            ['key' => 'coolant', 'name' => 'Coolant Flush', 'category' => 'engine', 'default_interval_months' => 60, 'default_interval_miles' => 60000],
            ['key' => 'transmission-fluid', 'name' => 'Transmission Fluid', 'category' => 'drivetrain', 'default_interval_months' => 72, 'default_interval_miles' => 60000],
            ['key' => 'spark-plugs', 'name' => 'Spark Plugs', 'category' => 'engine', 'default_interval_months' => null, 'default_interval_miles' => 60000],
            ['key' => 'serpentine-belt', 'name' => 'Serpentine Belt', 'category' => 'engine', 'default_interval_months' => 60, 'default_interval_miles' => 70000],
            ['key' => 'differential-fluid', 'name' => 'Differential Fluid', 'category' => 'drivetrain', 'default_interval_months' => 48, 'default_interval_miles' => 45000],
            ['key' => 'inspection', 'name' => 'State Inspection', 'category' => 'admin', 'default_interval_months' => 12, 'default_interval_miles' => null],
            ['key' => 'registration', 'name' => 'Registration Renewal', 'category' => 'admin', 'default_interval_months' => 12, 'default_interval_miles' => null],
            ['key' => 'emissions', 'name' => 'Emissions Test', 'category' => 'admin', 'default_interval_months' => 24, 'default_interval_miles' => null],
            ['key' => 'detail', 'name' => 'Detail / Wash', 'category' => 'exterior', 'default_interval_months' => 6, 'default_interval_miles' => null],
            ['key' => 'other', 'name' => 'Other Service', 'category' => 'other', 'default_interval_months' => null, 'default_interval_miles' => null],
        ];
    }
}
