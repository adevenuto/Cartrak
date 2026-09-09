<?php

namespace App\Http\Controllers;

use App\Models\VehiclePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves vehicle photos from the private disk, behind the owner check.
 *
 * These files deliberately do not live on a public disk: a car photo routinely
 * catches a licence plate or the house behind it, and the brief calls this a
 * private, single-owner garage. The cost is one PHP boot per photo on a cold
 * cache, which the immutable cache header below reduces to once per photo per
 * browser.
 */
class VehiclePhotoController extends Controller
{
    public function show(Request $request, VehiclePhoto $photo): BinaryFileResponse
    {
        return $this->stream($request, $photo, $photo->path);
    }

    public function thumbnail(Request $request, VehiclePhoto $photo): BinaryFileResponse
    {
        return $this->stream($request, $photo, $photo->thumbnail_path);
    }

    private function stream(Request $request, VehiclePhoto $photo, string $path): BinaryFileResponse
    {
        Gate::authorize('view', $photo->vehicle);

        $disk = Storage::disk(Config::string('vehicles.photos.disk'));

        // The row exists but the bytes do not — a partially-failed write, or a
        // file removed underneath us. 404 rather than a 500.
        abort_unless($disk->exists($path), 404);

        // response()->file() rather than Storage::download()/serve(): the
        // adapter returns a StreamedResponse with no Last-Modified, no ETag and
        // no 304 path, which defeats the point of caching a private file.
        // BinaryFileResponse also supports range requests and can later be
        // offloaded to the web server via X-Sendfile without touching this code.
        //
        // NOTE: $disk->path() is local-adapter only. Moving photos to S3 would
        // mean switching to a temporaryUrl() redirect, which changes the auth
        // model — that is the migration point.
        $response = response()->file($disk->path($path), [
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

        $response->isNotModified($request);

        return $response;
    }
}
