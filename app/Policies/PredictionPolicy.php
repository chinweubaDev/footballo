<?php

namespace App\Policies;

use App\Models\Prediction;
use App\Models\User;

/**
 * Authorization for prediction administration.
 *
 * The application currently uses a single `is_admin` flag; these granular
 * abilities exist so finer-grained roles can be introduced later without
 * changing controllers.
 */
class PredictionPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }

    public function override(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }

    public function publish(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }

    public function feature(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }

    public function lock(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }

    public function unlock(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }

    public function revert(User $user, Prediction $prediction): bool
    {
        return (bool) $user->is_admin;
    }
}
