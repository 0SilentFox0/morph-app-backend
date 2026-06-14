<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClientPackage;

class ClientPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function view(User $user, ClientPackage $package): bool
    {
        if ($user->role === 'trainer' && $package->trainer_id === $user->id) {
            return true;
        }

        // Allow client user to view their own package
        $client = $package->client;

        return $client !== null && $client->user_id !== null && $client->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    public function archive(User $user, ClientPackage $package): bool
    {
        return $user->role === 'trainer' && $package->trainer_id === $user->id;
    }
}
