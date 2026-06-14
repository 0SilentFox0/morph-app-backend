<?php

namespace App\Events;

use App\Models\WorkoutLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkoutLogUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorkoutLog $log,
        public readonly string $actorId,
        public readonly int $version,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('session.' . $this->log->session_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'workout_log.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'workout_log_id' => $this->log->id,
            'session_id'     => $this->log->session_id,
            'actor_id'       => $this->actorId,
            'version'        => $this->version,
        ];
    }
}
