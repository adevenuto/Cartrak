<?php

namespace App\Concerns;

use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

/**
 * Photo rules and typed accessors shared by the store and update requests.
 *
 * The accessors exist for the same reason StoreEventRequest has eventAttributes():
 * they keep the controller out of validated() internals. They also keep PHPStan
 * happy — $request->file('photos') is typed UploadedFile|array|null, which level
 * 7 will not let you foreach.
 */
trait HandlesVehiclePhotos
{
    /**
     * Deliberately string rules rather than the File::image() builder.
     *
     * File::types() is declared `public static` and returns a fresh instance, so
     * File::image()->max(10240)->types([...]) silently DISCARDS the max. Only
     * one ordering is safe, which is a trap not worth leaving in the codebase.
     *
     * Four rules rather than `image`, each pulling its weight:
     *   - `image` alone would also permit gif, bmp, webp, avif and heic.
     *   - `mimetypes` is content-sniffed for a real upload; the actual gate.
     *   - `mimes` is extension-derived, and gives the friendlier message.
     *   - `dimensions` is the only rule that opens the bytes (@getimagesize), so
     *     it doubles as "is this genuinely a decodable image".
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function photoRules(): array
    {
        $max = Config::integer('vehicles.photos.max_per_vehicle');
        $dimension = Config::integer('vehicles.photos.max_dimension');

        return [
            'photos' => ['nullable', 'array', 'max:'.$max],
            'photos.*' => [
                'file',
                'mimetypes:image/jpeg,image/png',
                'mimes:jpeg,jpg,png',
                'max:'.Config::integer('vehicles.photos.max_kilobytes'),
                "dimensions:max_width={$dimension},max_height={$dimension}",
            ],

            // Scoping the exists rule to this vehicle is what turns "delete
            // someone else's photo by id" into a validation failure rather than
            // an authorization hole.
            'removed_photo_ids' => ['nullable', 'array'],
            'removed_photo_ids.*' => [
                'integer',
                Rule::exists('vehicle_photos', 'id')->where(
                    'vehicle_id',
                    $this->routeVehicle()?->id,
                ),
            ],

            // The full desired order, as "existing:{id}" or "new:{index}".
            // new:{index} points into photos[], which is why the picker rebuilds
            // the file input in strip order.
            'photo_order' => ['nullable', 'array', 'max:'.$max],
            'photo_order.*' => ['string', 'regex:/^(existing:\d+|new:\d+)$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function photoMessages(): array
    {
        $max = Config::integer('vehicles.photos.max_per_vehicle');

        return [
            'photos.max' => "A vehicle can hold {$max} photos. Remove one before adding another.",
            'photos.*.mimetypes' => 'Photos need to be JPEG or PNG.',
            'photos.*.mimes' => 'Photos need to be JPEG or PNG.',
            'photos.*.max' => 'That photo is over 10 MB. Crop it, or shoot at a smaller size.',
            'photos.*.dimensions' => 'That photo is bigger than 6000 × 6000 pixels. Shoot at 12 MP, or crop it first.',
            'photos.*.file' => 'That photo did not upload cleanly. Try again.',
            'removed_photo_ids.*.exists' => 'That photo is not on this vehicle.',
        ];
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function photos(): array
    {
        // Flatten first: a malformed multipart body can nest, and this way the
        // instanceof guard below is doing real work rather than restating a
        // type the docblock already promised.
        $files = Arr::flatten([$this->file('photos', [])]);

        return array_values(array_filter(
            $files,
            fn (mixed $file): bool => $file instanceof UploadedFile,
        ));
    }

    /**
     * @return array<int, int>
     */
    public function removedPhotoIds(): array
    {
        $ids = $this->input('removed_photo_ids', []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_map(intval(...), array_filter($ids, is_numeric(...))));
    }

    /**
     * @return array<int, string>
     */
    public function photoOrder(): array
    {
        $order = $this->input('photo_order', []);

        if (! is_array($order)) {
            return [];
        }

        return array_values(array_filter($order, is_string(...)));
    }

    protected function routeVehicle(): ?Vehicle
    {
        $vehicle = $this->route('vehicle');

        return $vehicle instanceof Vehicle ? $vehicle : null;
    }
}
