<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainer_id' => $this->trainer_id,
            'name' => $this->name,
            'description' => $this->description,
            'difficulty' => $this->difficulty,
            'estimated_duration_min' => $this->estimated_duration_min,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'exercises' => ProgramExerciseResource::collection($this->whenLoaded('exercises')),
        ];
    }
}
