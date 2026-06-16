<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutLogSetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'workout_log_exercise_id' => $this->workout_log_exercise_id,
            'exercise_id'             => $this->exercise_id,
            'set_index'               => $this->set_index,
            'reps'                    => $this->reps,
            'weight_kg'               => $this->weight_kg,
            'rest_seconds'            => $this->rest_seconds,
            'performed_at'            => $this->performed_at?->toIso8601String(),
            'actor_user_id'           => $this->actor_user_id,
            'is_pr'                   => $this->is_pr,
            'client_uuid'             => $this->client_uuid,
            'version'                 => $this->version,
            'created_at'              => $this->created_at?->toIso8601String(),
        ];
    }
}
