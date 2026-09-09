<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleObserver
{
    /**
     * Delete a vehicle's photo files when the vehicle goes.
     *
     * The foreign key is cascadeOnDelete, which happens inside the DATABASE and
     * fires no model events — so VehiclePhotoObserver would never run and every
     * file would be orphaned. Deleting the rows individually here keeps the
     * single cleanup path in that observer.
     *
     * The directory sweep afterwards is belt-and-braces for anything the
     * database did not know about, which is exactly what sharding files under
     * vehicle-photos/{id}/ buys.
     *
     * Hooks `deleting`, not `deleted`, so the relation is still queryable.
     */
    public function deleting(Vehicle $vehicle): void
    {
        $vehicle->photos()->each(function (VehiclePhoto $photo): void {
            $photo->delete();
        });

        $disk = Config::string('vehicles.photos.disk');
        $directory = Config::string('vehicles.photos.directory')."/{$vehicle->id}";

        DB::afterCommit(function () use ($disk, $directory): void {
            Storage::disk($disk)->deleteDirectory($directory);
        });
    }
}
