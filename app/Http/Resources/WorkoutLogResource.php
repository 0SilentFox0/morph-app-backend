<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'session_id'          => $this->session_id,
            'started_at'          => $this->started_at?->toIso8601String(),
            'started_by_user_id'  => $this->started_by_user_id,
            'finished_at'         => $this->finished_at?->toIso8601String(),
            'finished_by_user_id' => $this->finished_by_user_id,
            'last_version'        => $this->last_version,
            'created_at'          => $this->created_at?->toIso8601String(),
            'exercises'           => WorkoutLogExerciseResource::collection($this->whenLoaded('exercises')),
        ];
    }
}
