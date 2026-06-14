<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'program_id' => $this->program_id,
            'program_snapshot' => $this->program_snapshot,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'removed_at' => $this->removed_at?->toISOString(),
        ];
    }
}
