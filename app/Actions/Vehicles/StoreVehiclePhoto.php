<?php

namespace App\Actions\Vehicles;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns one uploaded JPEG/PNG into the two WebP files a vehicle photo is made
 * of, and records the row that points at them.
 *
 * Nothing user-supplied is ever stored verbatim: every upload is decoded and
 * re-encoded, which normalises the format, strips all metadata (EXIF GPS
 * included) and caps the dimensions in one pass.
 *
 * Runs synchronously. The queue is configured but nothing consumes it — no
 * worker, no scheduler, and `composer dev` starts neither — so a queued job
 * would simply never run. Queueing would also mean storing the original first,
 * which is the opposite of compressing on the way in. If that changes, this
 * class is the seam: wrap it in a job, add a status column, and nothing else
 * moves.
 */
class StoreVehiclePhoto
{
    /**
     * @throws ImageException when the bytes cannot be decoded as an image.
     */
    public function handle(Vehicle $vehicle, UploadedFile $file, int $position = 0): VehiclePhoto
    {
        $longEdge = $this->dimension('long_edge');
        $thumbEdge = $this->dimension('thumbnail_edge');
        $quality = $this->quality();
        $disk = Config::string('vehicles.photos.disk');

        // orient() MUST come first. EXIF orientation lives in JPEG metadata and
        // the WebP encoder discards metadata, so the rotation has to be baked
        // into the pixels before encoding — there is no second chance, and the
        // failure mode is every phone photo stored sideways.
        //
        // scale() maps to Intervention's scaleDown, which fits the image inside
        // the box and never enlarges, so a small source is left alone.
        $full = Image::fromUpload($file)
            ->orient()
            ->scale($longEdge, $longEdge)
            ->toWebp()
            ->quality($quality);

        $bytes = $full->toBytes();

        // Derive the thumbnail from the already-encoded bytes, not from the
        // original. Image is immutable-with-clone, so going back to the source
        // would decode the full-size file a second time — ~144 MB rather than
        // ~10 MB for a 36 MP input.
        $thumbnailBytes = Image::fromBytes($bytes)
            ->scale($thumbEdge, $thumbEdge)
            ->toWebp()
            ->quality($quality)
            ->toBytes();

        [$width, $height] = Image::fromBytes($bytes)->dimensions();

        $ulid = (string) Str::ulid();
        $directory = Config::string('vehicles.photos.directory')."/{$vehicle->id}";
        $path = "{$directory}/{$ulid}.webp";
        $thumbnailPath = "{$directory}/{$ulid}-thumb.webp";

        // Storage::put with the bytes we already have, rather than
        // $image->store(), which would re-enter toBytes() and name the file
        // randomly. A ULID stem keeps rows and files reconcilable.
        if (! Storage::disk($disk)->put($path, $bytes)) {
            throw new ImageException("Unable to write vehicle photo [{$path}].");
        }

        if (! Storage::disk($disk)->put($thumbnailPath, $thumbnailBytes)) {
            Storage::disk($disk)->delete($path);

            throw new ImageException("Unable to write vehicle photo thumbnail [{$thumbnailPath}].");
        }

        return $vehicle->photos()->create([
            'ulid' => $ulid,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'position' => $position,
            'width' => $width,
            'height' => $height,
            'bytes' => strlen($bytes),
            'placeholder_color' => Image::fromBytes($thumbnailBytes)->dominantColor(),
        ]);
    }

    /**
     * A configured pixel dimension, validated rather than assumed. A zero or
     * negative edge would make scale() throw deep inside the driver with a much
     * less useful message.
     *
     * @return int<1, max>
     */
    private function dimension(string $key): int
    {
        $value = Config::integer("vehicles.photos.{$key}");

        if ($value < 1) {
            throw new ImageException("Config [vehicles.photos.{$key}] must be at least 1 pixel.");
        }

        return $value;
    }

    /**
     * @return int<1, 100>
     */
    private function quality(): int
    {
        $value = Config::integer('vehicles.photos.quality');

        if ($value < 1 || $value > 100) {
            throw new ImageException('Config [vehicles.photos.quality] must be between 1 and 100.');
        }

        return $value;
    }
}
