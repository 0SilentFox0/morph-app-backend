<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutLogExerciseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'exercise_id'      => $this->exercise_id,
            'order'             => $this->order,
            'name_snapshot'     => $this->name_snapshot,
            'planned_sets'      => $this->planned_sets,
            'planned_reps'      => $this->planned_reps,
            'planned_weight_kg' => $this->planned_weight_kg,
            'sets'              => WorkoutLogSetResource::collection($this->whenLoaded('sets')),
        ];
    }
}
