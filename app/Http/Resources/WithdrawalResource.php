<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainer_id' => $this->trainer_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'withdrawn_at' => $this->withdrawn_at?->toIso8601String(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
