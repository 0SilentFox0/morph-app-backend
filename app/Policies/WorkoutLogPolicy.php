<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutLog;

class WorkoutLogPolicy
{
    public function view(User $user, WorkoutLog $log): bool
    {
        return $this->isTrainerOrParticipant($user, $log);
    }

    public function create(User $user, WorkoutLog $log): bool
    {
        return $this->isTrainerOrParticipant($user, $log);
    }

    public function update(User $user, WorkoutLog $log): bool
    {
        return $this->isTrainerOrParticipant($user, $log);
    }

    public function logSet(User $user, WorkoutLog $log): bool
    {
        return $this->isTrainerOrParticipant($user, $log);
    }

    private function isTrainerOrParticipant(User $user, WorkoutLog $log): bool
    {
        $session = $log->session;

        if (!$session) {
            return false;
        }

        // Trainer of the session
        if ($session->trainer_id === $user->id) {
            return true;
        }

        // Client participant
        return $session->participants()
            ->whereHas('client', function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->exists();
    }
}
