<?php

namespace App\Observers;

use App\Models\VehiclePhoto;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehiclePhotoObserver
{
    /**
     * Remove a photo's files once its row is gone.
     *
     * This lives in an observer rather than in the controller so it is correct
     * regardless of how the row was deleted — a form submit, tinker, a future
     * bulk-cleanup command, or a factory teardown.
     *
     * DB::afterCommit is the crux. Deleting the files inline would be wrong: if
     * the surrounding transaction rolls back, the row returns and the bytes are
     * already gone — a permanently broken photo. afterCommit defers to the real
     * commit, and runs immediately when there is no transaction, so both call
     * sites behave.
     */
    public function deleted(VehiclePhoto $photo): void
    {
        $paths = [$photo->path, $photo->thumbnail_path];
        $disk = Config::string('vehicles.photos.disk');

        DB::afterCommit(function () use ($disk, $paths): void {
            Storage::disk($disk)->delete($paths);
        });
    }
}
