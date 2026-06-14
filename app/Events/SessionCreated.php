<?php

namespace App\Events;

use App\Models\TrainingSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly TrainingSession $session,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        $this->session->loadMissing('participants.client');

        foreach ($this->session->participants as $participant) {
            if ($participant->client && $participant->client->user_id) {
                $channels[] = new PrivateChannel('user.' . $participant->client->user_id);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'session.created';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'title'      => $this->session->title,
            'start_at'   => $this->session->start_at?->toIso8601String(),
            'end_at'     => $this->session->end_at?->toIso8601String(),
            'status'     => $this->session->status,
            'trainer_id' => $this->session->trainer_id,
        ];
    }
}
