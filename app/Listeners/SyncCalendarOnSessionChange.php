<?php

namespace App\Listeners;

use App\Events\SessionCreated;
use App\Events\SessionStatusChanged;
use App\Events\SessionUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Attribute\AsEventHandler;

class SyncCalendarOnSessionChange implements ShouldQueue
{
    public function handleSessionCreated(SessionCreated $event): void
    {
        // Stub: dispatch async job for Google Calendar sync
        // SyncCalendarJob::dispatch($event->session, 'create');
    }

    public function handleSessionUpdated(SessionUpdated $event): void
    {
        // Stub: dispatch async job for Google Calendar sync
        // SyncCalendarJob::dispatch($event->session, 'update');
    }

    public function handleSessionStatusChanged(SessionStatusChanged $event): void
    {
        // Stub: dispatch async job for Google Calendar sync
        // SyncCalendarJob::dispatch($event->session, 'status_change');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            SessionCreated::class       => 'handleSessionCreated',
            SessionUpdated::class       => 'handleSessionUpdated',
            SessionStatusChanged::class => 'handleSessionStatusChanged',
        ];
    }
}
