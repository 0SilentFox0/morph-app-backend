<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SessionRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Stub: find sessions starting within the next 30 minutes
        // and send push notifications to participants

        $upcomingSessions = TrainingSession::query()
            ->where('status', 'planned')
            ->whereBetween('start_at', [
                Carbon::now(),
                Carbon::now()->addMinutes(30),
            ])
            ->with('participants.client')
            ->get();

        foreach ($upcomingSessions as $session) {
            foreach ($session->participants as $participant) {
                // Stub: send push notification to participant
                // NotificationService::sendPush($participant->client->user_id, ...)
            }
        }
    }
}
