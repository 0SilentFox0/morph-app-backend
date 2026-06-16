<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->trainer_id,
            'trainer_id' => $this->trainer_id,
            'client_id' => $this->client_id,
            'client_package_id' => $this->client_package_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'method' => $this->method,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
