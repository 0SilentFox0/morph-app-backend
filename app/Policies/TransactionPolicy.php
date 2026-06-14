<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->role === 'trainer' && $transaction->trainer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->role === 'trainer' && $transaction->trainer_id === $user->id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'trainer' && $transaction->trainer_id === $user->id;
    }
}
