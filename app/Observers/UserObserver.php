<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Vehicle;

class UserObserver
{
    /**
     * Delete a user's vehicles individually before the user goes.
     *
     * The vehicles.user_id foreign key is cascadeOnDelete, which happens inside
     * the DATABASE and fires no model events — so VehicleObserver would never
     * run and every photo file for every vehicle would be orphaned on disk.
     * Account deletion is a real feature (Settings -> Profile), so this is not
     * hypothetical.
     *
     * Hooks `deleting`, not `deleted`, so the relation is still queryable.
     */
    public function deleting(User $user): void
    {
        $user->vehicles()->each(function (Vehicle $vehicle): void {
            $vehicle->delete();
        });
    }
}
