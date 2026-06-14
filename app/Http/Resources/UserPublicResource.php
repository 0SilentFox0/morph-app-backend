<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array (limited fields for public view).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'avatar_url'     => $this->avatar_url,
            'role'           => $this->role,
            'experience'     => $this->experience,
            'certifications' => $this->certifications,
            'training_types' => $this->training_types,
        ];
    }
}
