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
            'category' => $this->category,
            'price' => $this->price,
            'price_currency' => $this->price_currency,
            'estimated_duration_min' => $this->estimated_duration_min,
            'cover_url' => $this->whenLoaded('coverFile', fn () => $this->coverFile?->s3_key),
            'cover_file_id' => $this->cover_file_id,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'exercises' => ProgramExerciseResource::collection($this->whenLoaded('exercises')),
        ];
    }
}
