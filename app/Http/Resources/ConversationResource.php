<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUserId = $request->user()?->id;

        $unreadCount = 0;
        if ($currentUserId && $this->relationLoaded('participants')) {
            $participant = $this->participants->firstWhere('user_id', $currentUserId);
            if ($participant) {
                $query = $this->messages();
                if ($participant->last_read_message_id) {
                    $lastReadMessage = $this->messages()->find($participant->last_read_message_id);
                    if ($lastReadMessage) {
                        $query = $query->where('sent_at', '>', $lastReadMessage->sent_at);
                    }
                }
                $unreadCount = $query->where('sender_id', '!=', $currentUserId)->count();
            }
        }

        return [
            'id'              => $this->id,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'participants'    => $this->whenLoaded('participants', function () {
                return $this->participants->map(fn ($p) => [
                    'user_id'      => $p->user_id,
                    'last_read_at' => $p->last_read_at?->toIso8601String(),
                ]);
            }),
            'last_message'    => new MessageResource($this->whenLoaded('lastMessage')),
            'unread_count'    => $unreadCount,
        ];
    }
}
