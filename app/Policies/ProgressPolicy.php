<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BodyMeasurement;

class ProgressPolicy
{
    /**
     * Determine if the user can view progress data for a given client.
     */
    public function view(User $user, string $clientId): bool
    {
        // Trainer who owns the client
        if ($user->role === 'trainer') {
            return \App\Models\Client::where('id', $clientId)
                ->where('trainer_id', $user->id)
                ->exists();
        }

        // Client user linked to the client record
        return \App\Models\Client::where('id', $clientId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine if the user can create progress data for a given client.
     */
    public function create(User $user, string $clientId): bool
    {
        if ($user->role === 'trainer') {
            return \App\Models\Client::where('id', $clientId)
                ->where('trainer_id', $user->id)
                ->exists();
        }

        return \App\Models\Client::where('id', $clientId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine if the user can delete a measurement.
     */
    public function delete(User $user, BodyMeasurement $measurement): bool
    {
        if ($user->role === 'trainer') {
            $client = $measurement->client;

            return $client !== null && $client->trainer_id === $user->id;
        }

        return false;
    }
}
