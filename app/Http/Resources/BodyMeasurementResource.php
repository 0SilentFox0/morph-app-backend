<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BodyMeasurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'metric_type' => $this->metric_type,
            'value' => $this->value,
            'unit' => $this->unit,
            'measured_at' => $this->measured_at?->toIso8601String(),
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
