<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TrainingSession;

class SessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TrainingSession $session): bool
    {
        if ($session->trainer_id === $user->id) {
            return true;
        }

        return $session->participants()
            ->whereHas('client', function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isTrainer();
    }

    public function update(User $user, TrainingSession $session): bool
    {
        return $session->trainer_id === $user->id;
    }

    public function cancel(User $user, TrainingSession $session): bool
    {
        if ($session->trainer_id === $user->id) {
            return true;
        }

        return $session->participants()
            ->whereHas('client', function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->exists();
    }

    public function delete(User $user, TrainingSession $session): bool
    {
        return $session->trainer_id === $user->id
            && $session->status === 'planned';
    }
}
