<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'email'                  => $this->email,
            'name'                   => $this->name,
            'avatar_url'             => $this->avatar_url,
            'role'                   => $this->role,
            'timezone'               => $this->timezone,
            'locale'                 => $this->locale,
            'currency'               => $this->currency,
            'points'                 => $this->points,
            'experience'             => $this->experience,
            'certifications'         => $this->certifications,
            'training_types'         => $this->training_types,
            'client_types'           => $this->client_types,
            'locations'              => $this->locations,
            'work_schedule_start'    => $this->work_schedule_start,
            'work_schedule_end'      => $this->work_schedule_end,
            'work_schedule_days'     => $this->work_schedule_days,
            'goals'                  => $this->goals,
            'fitness_level'          => $this->fitness_level,
            'onboarding_completed_at' => $this->onboarding_completed_at,
            'created_at'             => $this->created_at,
        ];
    }
}
