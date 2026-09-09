<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<VehiclePhoto>
 */
class VehiclePhotoFactory extends Factory
{
    protected $model = VehiclePhoto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ulid = (string) Str::ulid();

        return [
            'vehicle_id' => Vehicle::factory(),
            'ulid' => $ulid,
            'path' => "vehicle-photos/1/{$ulid}.webp",
            'thumbnail_path' => "vehicle-photos/1/{$ulid}-thumb.webp",
            'position' => 0,
            'width' => 1600,
            'height' => 900,
            'bytes' => 120_000,
            'placeholder_color' => '#484e87',
        ];
    }

    /**
     * Put real bytes on the (faked) disk at the paths this row points at, so
     * delete and serve tests exercise the filesystem without pushing files
     * through the HTTP layer.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (VehiclePhoto $photo): void {
            $photo->forceFill([
                'path' => $this->pathFor($photo, ''),
                'thumbnail_path' => $this->pathFor($photo, '-thumb'),
            ])->save();

            $disk = Storage::disk(Config::string('vehicles.photos.disk'));

            $disk->put($photo->path, 'fake-webp-bytes');
            $disk->put($photo->thumbnail_path, 'fake-webp-thumb-bytes');
        });
    }

    private function pathFor(VehiclePhoto $photo, string $suffix): string
    {
        $directory = Config::string('vehicles.photos.directory');

        return "{$directory}/{$photo->vehicle_id}/{$photo->ulid}{$suffix}.webp";
    }
}
