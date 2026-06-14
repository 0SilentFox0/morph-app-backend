<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Exercise;

class ExercisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function view(User $user, Exercise $exercise): bool
    {
        return $user->role === 'trainer' && $exercise->trainer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $user->role === 'trainer' && $exercise->trainer_id === $user->id;
    }

    public function archive(User $user, Exercise $exercise): bool
    {
        return $user->role === 'trainer' && $exercise->trainer_id === $user->id;
    }
}
