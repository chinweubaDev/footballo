<?php

namespace App\Policies;

use App\Models\BacktestRun;
use App\Models\User;

/**
 * Authorization for backtest administration. Only administrators may start,
 * cancel, view or archive backtests.
 */
class BacktestPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, BacktestRun $run): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function cancel(User $user, BacktestRun $run): bool
    {
        return (bool) $user->is_admin;
    }

    public function archive(User $user, BacktestRun $run): bool
    {
        return (bool) $user->is_admin;
    }
}
