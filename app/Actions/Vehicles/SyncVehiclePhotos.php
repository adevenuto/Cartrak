<?php

namespace App\Actions\Vehicles;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Applies one form submit's worth of photo changes: new uploads, removals, and
 * the resulting order. One path for both creating and updating a vehicle.
 *
 * The ordering of operations is the whole design. Image processing is slow —
 * seconds, for six large files — and a database transaction cannot roll back a
 * filesystem write. So the files are written FIRST, outside any transaction,
 * and the transaction is kept to the milliseconds of deletes and renumbering
 * that actually need atomicity. On failure the paths written during this
 * request are deleted explicitly, which is an exact compensation because every
 * one of them was recorded as it happened.
 */
class SyncVehiclePhotos
{
    public function __construct(private StoreVehiclePhoto $storePhoto) {}

    /**
     * @param  array<int, UploadedFile>  $newFiles
     * @param  array<int, string>  $order  entries of "existing:{id}" or "new:{index}"
     * @param  array<int, int>  $removedIds
     */
    public function handle(Vehicle $vehicle, array $newFiles, array $order = [], array $removedIds = []): void
    {
        if ($newFiles === [] && $order === [] && $removedIds === []) {
            return;
        }

        $written = [];
        $created = [];

        try {
            foreach ($newFiles as $index => $file) {
                $photo = $this->storePhoto->handle($vehicle, $file);

                $created[$index] = $photo;
                $written[] = $photo->path;
                $written[] = $photo->thumbnail_path;
            }

            DB::transaction(function () use ($vehicle, $order, $removedIds, $created): void {
                // Deleted one row at a time on purpose: a mass whereIn delete
                // fires no model events, so the observer would never run and
                // every file would be orphaned.
                $vehicle->photos()->whereIn('id', $removedIds)->get()
                    ->each(function (VehiclePhoto $photo): void {
                        $photo->delete();
                    });

                $this->applyOrder($vehicle, $order, $created);
            });
        } catch (Throwable $e) {
            Storage::disk(Config::string('vehicles.photos.disk'))->delete($written);

            throw $e;
        }
    }

    /**
     * Renumber every surviving photo to 0..n-1 in the requested order.
     *
     * Deterministic renumbering in a single pass is what makes the non-unique
     * (vehicle_id, position) index safe, and guarantees exactly one photo sits
     * at position 0 — which is the card image.
     *
     * Anything the order does not mention is appended in its current order
     * rather than dropped, so a missing or partial ordering hint can never lose
     * a photo.
     *
     * @param  array<int, string>  $order
     * @param  array<int, VehiclePhoto>  $created
     */
    private function applyOrder(Vehicle $vehicle, array $order, array $created): void
    {
        $survivors = $vehicle->photos()->get()->keyBy('id');
        $ordered = [];

        foreach ($order as $token) {
            [$kind, $reference] = explode(':', $token, 2);

            $photo = match ($kind) {
                'existing' => $survivors->get((int) $reference),
                'new' => $created[(int) $reference] ?? null,
                default => null,
            };

            if ($photo instanceof VehiclePhoto && ! in_array($photo->id, array_column($ordered, 'id'), true)) {
                $ordered[] = $photo;
            }
        }

        $mentioned = array_column($ordered, 'id');

        foreach ($survivors as $photo) {
            if (! in_array($photo->id, $mentioned, true)) {
                $ordered[] = $photo;
            }
        }

        foreach ($ordered as $position => $photo) {
            if ($photo->position !== $position) {
                $photo->forceFill(['position' => $position])->save();
            }
        }
    }
}
