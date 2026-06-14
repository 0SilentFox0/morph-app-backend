<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'exercise_id' => $this->exercise_id,
            'weight_kg' => $this->weight_kg,
            'reps' => $this->reps,
            'estimated_1rm' => $this->estimated_1rm,
            'achieved_at' => $this->achieved_at?->toISOString(),
            'exercise' => $this->whenLoaded('exercise', fn () => new \App\Http\Resources\ExerciseResource($this->exercise)),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
