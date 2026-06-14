<?php

namespace App\Events;

use App\Models\User;
use App\Models\TrainingSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly TrainingSession $session,
        public readonly string $oldStatus,
        public readonly string $newStatus,
        public readonly User $changedBy,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('session.' . $this->session->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'session.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id'  => $this->session->id,
            'old_status'  => $this->oldStatus,
            'new_status'  => $this->newStatus,
            'changed_by'  => $this->changedBy->id,
            'changed_at'  => $this->session->status_changed_at?->toIso8601String(),
        ];
    }
}
