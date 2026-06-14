<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->role === 'trainer' && $withdrawal->trainer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        return $user->role === 'trainer' && $withdrawal->trainer_id === $user->id;
    }
}
