<?php

namespace App\Http\Controllers;

use App\Models\VehiclePhoto;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Serves vehicle photos from the private disk, behind the owner check.
 *
 * These files deliberately do not live on a public disk: a car photo routinely
 * catches a licence plate or the house behind it, and the brief calls this a
 * private, single-owner garage. The cost is one PHP boot per photo on a cold
 * cache, which the immutable cache header below reduces to once per photo per
 * browser.
 *
 * In production the disk is a private Laravel Cloud object storage bucket, so
 * the bytes are read from the bucket and streamed through this controller. A
 * temporaryUrl() redirect would take the traffic off the app, but anyone holding
 * the link could view the photo until it expired — streaming keeps the owner
 * check on every request.
 */
class VehiclePhotoController extends Controller
{
    public function show(Request $request, VehiclePhoto $photo): StreamedResponse
    {
        return $this->stream($request, $photo, $photo->path);
    }

    public function thumbnail(Request $request, VehiclePhoto $photo): StreamedResponse
    {
        return $this->stream($request, $photo, $photo->thumbnail_path);
    }

    private function stream(Request $request, VehiclePhoto $photo, string $path): StreamedResponse
    {
        Gate::authorize('view', $photo->vehicle);

        $response = new StreamedResponse(null, 200, [
            'Content-Type' => 'image/webp',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        // The ULID identifies the bytes as well as a hash would, and costs no
        // file read, precisely because the content never changes.
        $response->setEtag($photo->ulid);

        // Set caching through Symfony's API, NOT as a raw Cache-Control string.
        // Symfony recomputes that header when an ETag is present and will
        // happily rewrite a hand-written "private" to "public" — which on a
        // private file means a shared proxy could cache and serve one user's
        // photo to another. setPrivate() survives the recomputation.
        $response->setPrivate();
        $response->setMaxAge(604800);
        $response->headers->addCacheControlDirective('immutable');

        // Answer a revalidation before touching storage at all. On a bucket that
        // saves a round trip per cached photo, and the owner check above has
        // already run.
        if ($response->isNotModified($request)) {
            return $response;
        }

        // The row exists but the bytes do not — a partially-failed write, or a
        // file removed underneath us. 404 rather than a 500.
        $stream = $this->open(
            Storage::disk(Config::string('vehicles.photos.disk')),
            $path,
        );

        abort_if($stream === null, 404);

        $response->setCallback(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }

    /**
     * Open a read stream, treating a missing object as null.
     *
     * Deliberately not $disk->path(): that resolves to a real file only on the
     * local adapter, and on an object storage bucket it names a key that does
     * not exist on the application's filesystem.
     *
     * @return resource|null
     */
    private function open(Filesystem $disk, string $path)
    {
        try {
            return $disk->readStream($path);
        } catch (Throwable) {
            // A disk configured with 'throw' => true raises instead of returning
            // null; either way the photo is unavailable.
            return null;
        }
    }
}
