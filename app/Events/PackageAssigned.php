<?php

namespace App\Events;

use App\Models\ClientPackage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageAssigned implements ShouldBroadcast
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
        $channels = [];

        $clientUserId = $this->resolveClientUserId();

        if ($clientUserId) {
            $channels[] = new PrivateChannel("user.{$clientUserId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'package.assigned';
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
            'status' => $this->package->status,
        ];
    }

    private function resolveClientUserId(): ?string
    {
        $client = $this->package->client;

        return $client?->user_id;
    }
}
