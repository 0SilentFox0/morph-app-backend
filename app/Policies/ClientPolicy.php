<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->role === 'trainer' && $client->trainer_id === $user->id) {
            return true;
        }

        return $client->user_id !== null && $client->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function update(User $user, Client $client): bool
    {
        return $user->role === 'trainer' && $client->trainer_id === $user->id;
    }

    public function archive(User $user, Client $client): bool
    {
        return $user->role === 'trainer' && $client->trainer_id === $user->id;
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->role === 'trainer' && $client->trainer_id === $user->id;
    }

    public function invite(User $user, Client $client): bool
    {
        return $user->role === 'trainer' && $client->trainer_id === $user->id;
    }
}
