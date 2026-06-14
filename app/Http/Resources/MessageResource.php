<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'conversation_id'   => $this->conversation_id,
            'sender_id'         => $this->sender_id,
            'body'              => $this->body,
            'media_file_ids'    => $this->media_file_ids,
            'sent_at'           => $this->sent_at?->toIso8601String(),
            'deleted_at'        => $this->deleted_at?->toIso8601String(),
        ];
    }
}
