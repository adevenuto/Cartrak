# Vehicle photos — operational notes

Photos are stored **private** (`storage/app/private/vehicle-photos/{vehicle_id}/`)
and served through `GET /vehicle-photos/{ulid}` behind the owner check, not from a
public symlink. `php artisan storage:link` is deliberately **not** required.

## Pipeline

Uploads are accepted as JPEG or PNG (≤10 MB, ≤6000×6000) and immediately
re-encoded, so nothing user-supplied is ever served back verbatim:

```
Image::fromUpload($file)->orient()->scale(1600, 1600)->toWebp()->quality(82)
```

`orient()` runs first because EXIF orientation lives in JPEG metadata and the
WebP encoder discards metadata — the rotation has to be baked into pixels before
encoding or every phone photo is stored sideways. Re-encoding also strips EXIF
GPS coordinates, which matters for photos taken on a driveway.

A 400px thumbnail is derived from the already-encoded full-size bytes (not from
the original, which would decode the source a second time), and the dominant
colour is stored so tiles paint the right colour before the image loads.

Measured on a 3000×2000 / 209 KB source: **1600×1067 WebP at 7.4 KB in ~240 ms**,
about 3.6% of the original.

## PHP limits

`php artisan serve` uses the CLI binary, so local dev inherits the CLI ini. If the
app is ever served through **php-fpm, Herd or Valet, that is a separate ini** and
must be checked in a browser via `phpinfo()` — not with `php -i`.

Recommended for the web SAPI:

| Setting | Value | Why |
|---|---|---|
| `memory_limit` | `512M` | GD decodes a 36 MP source to ~144 MB, doubled if EXIF rotation clones it |
| `upload_max_filesize` | `12M` | above the 10 MB rule, so oversized files fail *validation* with a readable message |
| `post_max_size` | `80M` | six 12 MB files plus slack |
| `max_file_uploads` | `>= 10` | the cap is six photos per vehicle |

Current CLI values: all of `memory_limit`, `upload_max_filesize` and
`post_max_size` are `512M`, `max_file_uploads` is `20`.

**Why `upload_max_filesize` must exceed the app's own 10 MB rule:** a body over
`post_max_size` leaves `$_POST` and `$_FILES` both empty, so no validation rule
runs at all. Laravel's `ValidatePostSize` turns that into a 413, and
`bootstrap/app.php` renders it as a form error for Inertia rather than a raw
error modal — but a readable per-file message only happens when the request
actually reaches validation.

## Cleanup

Files are deleted by observers, not by controllers, so cleanup is correct no
matter how a row disappears:

- `VehiclePhotoObserver::deleted` — removes both files, inside `DB::afterCommit`
  so a rolled-back transaction cannot restore the row with the bytes already gone.
- `VehicleObserver::deleting` — the `vehicle_photos.vehicle_id` cascade happens in
  the database and fires no model events, so photos are deleted per-row first,
  then the vehicle directory is swept.
- `UserObserver::deleting` — same problem one level up: `vehicles.user_id` is also
  a database cascade, so account deletion would otherwise orphan every file.

The one case no observer can cover is a request that fails midway through an
upload; `SyncVehiclePhotos` tracks every path it writes and deletes them in a
`catch`.
