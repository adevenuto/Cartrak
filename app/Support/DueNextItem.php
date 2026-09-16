<?php

namespace App\Support;

/**
 * One row of the rail's "Due next" list.
 *
 * A small object rather than a nested array shape: PHPStan at level 7 wants a
 * value type for the collection either way, and an array-shape annotation for
 * this gets unwieldy and drifts from the thing it describes.
 */
readonly class DueNextItem
{
    public function __construct(
        public int $vehicleId,
        public string $vehicleName,
        public string $serviceName,
        public string $status,
        public int $percent,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'vehicle_name' => $this->vehicleName,
            'service_name' => $this->serviceName,
            'status' => $this->status,
            'percent' => $this->percent,
        ];
    }
}
