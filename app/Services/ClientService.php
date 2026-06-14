<?php

namespace App\Services;

use App\Models\User;
use App\Events\ClientCreated;
use App\Events\ClientInvitationSent;
use App\Models\Client;
use App\Models\ClientInvitation;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Str;

class ClientService
{
    public function list(User $trainer, array $filters = []): CursorPaginator
    {
        $query = Client::forTrainer($trainer->id);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $allowedSorts = ['name', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function create(User $trainer, array $data): Client
    {
        $data['trainer_id'] = $trainer->id;
        $data['status'] = $data['status'] ?? 'active';

        if (isset($data['email'])) {
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser) {
                $data['user_id'] = $existingUser->id;
            }
        }

        $client = Client::create($data);

        ClientCreated::dispatch($client);

        return $client;
    }

    public function show(Client $client): Client
    {
        return $client;
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->refresh();
    }

    public function archive(Client $client): Client
    {
        $client->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        return $client->refresh();
    }

    public function restore(Client $client): Client
    {
        $client->update([
            'status' => 'active',
            'archived_at' => null,
        ]);

        return $client->refresh();
    }

    public function invite(Client $client): ClientInvitation
    {
        $invitation = ClientInvitation::create([
            'client_id' => $client->id,
            'trainer_id' => $client->trainer_id,
            'code' => Str::random(32),
            'email' => $client->email,
            'expires_at' => now()->addDays(7),
            'last_sent_at' => now(),
        ]);

        ClientInvitationSent::dispatch($invitation);

        return $invitation;
    }

    public function acceptInvitation(string $code, User $user): Client
    {
        $invitation = ClientInvitation::where('code', $code)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $invitation->update([
            'accepted_at' => now(),
        ]);

        $client = $invitation->client;
        $client->update([
            'user_id' => $user->id,
        ]);

        return $client->refresh();
    }

    public function revokeInvitation(ClientInvitation $invitation): void
    {
        $invitation->update([
            'revoked_at' => now(),
        ]);
    }
}
