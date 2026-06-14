<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainer_id' => $this->trainer_id,
            'name' => $this->name,
            'kind' => $this->kind,
            'sessions_count' => $this->sessions_count,
            'validity_days' => $this->validity_days,
            'price' => $this->price,
            'currency' => $this->currency,
            'auto_renew_default' => $this->auto_renew_default,
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
