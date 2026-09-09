<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

/**
 * A vehicle has exactly one owner. There is no sharing, no household, and no
 * read-only guest — so every ability reduces to the same ownership check.
 */
class VehiclePolicy
{
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $vehicle->user_id === $user->id;
    }
}
