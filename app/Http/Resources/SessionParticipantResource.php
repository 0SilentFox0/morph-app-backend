<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionParticipantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'session_id' => $this->session_id,
            'client_id'  => $this->client_id,
            'client'     => $this->whenLoaded('client', fn () => [
                'id'         => $this->client->id,
                'name'       => $this->client->name,
                'avatar_url' => $this->client->avatar_url,
            ]),
        ];
    }
}
