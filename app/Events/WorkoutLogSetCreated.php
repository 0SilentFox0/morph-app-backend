<?php

namespace App\Events;

use App\Models\WorkoutLogSet;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkoutLogSetCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorkoutLogSet $set,
        public readonly string $actorId,
        public readonly int $version,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('session.' . $this->set->workoutLog->session_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'workout_log_set.created';
    }

    public function broadcastWith(): array
    {
        return [
            'set_id'                  => $this->set->id,
            'workout_log_id'          => $this->set->workout_log_id,
            'workout_log_exercise_id' => $this->set->workout_log_exercise_id,
            'exercise_id'             => $this->set->exercise_id,
            'set_index'               => $this->set->set_index,
            'reps'                    => $this->set->reps,
            'weight_kg'               => $this->set->weight_kg,
            'actor_id'                => $this->actorId,
            'version'                 => $this->version,
        ];
    }
}
