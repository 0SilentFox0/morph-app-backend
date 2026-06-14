<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exercise_id' => $this->exercise_id,
            'order' => $this->order,
            'sets' => $this->sets,
            'reps' => $this->reps,
            'weight_kg' => $this->weight_kg,
            'rest_seconds' => $this->rest_seconds,
            'notes' => $this->notes,
            'name_snapshot' => $this->name_snapshot,
            'exercise' => new ExerciseResource($this->whenLoaded('exercise')),
        ];
    }
}
