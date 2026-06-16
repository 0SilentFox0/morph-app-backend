<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'trainer_id' => $this->trainer_id,
            'template_id' => $this->template_id,
            'kind' => $this->kind,
            'sessions_count' => $this->sessions_count,
            'remaining_sessions' => $this->remaining_sessions,
            'validity_days' => $this->validity_days,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'auto_renew' => $this->auto_renew,
            'debt_since' => $this->debt_since?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
