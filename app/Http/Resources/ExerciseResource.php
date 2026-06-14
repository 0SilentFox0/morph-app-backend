<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainer_id' => $this->trainer_id,
            'name' => $this->name,
            'description' => $this->description,
            'muscle_groups' => $this->muscle_groups,
            'equipment' => $this->equipment,
            'video_file_id' => $this->video_file_id,
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
