<?php

namespace App\Events;

use App\Models\ClientPackage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageExhausted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ClientPackage $package,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("user.{$this->package->trainer_id}"),
        ];

        $clientUserId = $this->resolveClientUserId();

        if ($clientUserId) {
            $channels[] = new PrivateChannel("user.{$clientUserId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'package.exhausted';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'package_id' => $this->package->id,
            'client_id' => $this->package->client_id,
            'trainer_id' => $this->package->trainer_id,
            'kind' => $this->package->kind,
            'remaining_sessions' => $this->package->remaining_sessions,
        ];
    }

    private function resolveClientUserId(): ?string
    {
        $client = $this->package->client;

        return $client?->user_id;
    }
}
