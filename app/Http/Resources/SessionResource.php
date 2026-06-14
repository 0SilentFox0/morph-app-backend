<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'trainer_id'          => $this->trainer_id,
            'title'               => $this->title,
            'type'                => $this->type,
            'start_at'            => $this->start_at?->toIso8601String(),
            'end_at'              => $this->end_at?->toIso8601String(),
            'status'              => $this->status,
            'status_changed_at'   => $this->status_changed_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'notes'               => $this->notes,
            'program_id'          => $this->program_id,
            'client_package_id'   => $this->client_package_id,
            'series_id'           => $this->series_id,
            'google_event_id'     => $this->google_event_id,
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
            'participants'        => SessionParticipantResource::collection($this->whenLoaded('participants')),
        ];
    }
}
