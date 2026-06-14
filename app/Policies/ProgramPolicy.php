<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Program;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function view(User $user, Program $program): bool
    {
        if ($user->role === 'trainer' && $program->trainer_id === $user->id) {
            return true;
        }

        // Client can view if program is assigned to them
        return $program->clientPrograms()
            ->whereHas('client', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->whereNull('removed_at')
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function update(User $user, Program $program): bool
    {
        return $user->role === 'trainer' && $program->trainer_id === $user->id;
    }

    public function archive(User $user, Program $program): bool
    {
        return $user->role === 'trainer' && $program->trainer_id === $user->id;
    }
}
