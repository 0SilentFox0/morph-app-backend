<?php

namespace App\Services;

use App\Models\User;
use App\Events\SessionCreated;
use App\Events\SessionStatusChanged;
use App\Events\SessionUpdated;
use App\Models\SessionParticipant;
use App\Models\SessionSeries;
use App\Models\TrainingSession;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SessionService
{
    public function list(User $user, array $filters = []): CursorPaginator
    {
        $query = TrainingSession::query();

        if ($user->isTrainer()) {
            $query->forTrainer($user->id);
        } else {
            $query->whereHas('participants', function ($q) use ($user): void {
                $q->whereHas('client', function ($cq) use ($user): void {
                    $cq->where('user_id', $user->id);
                });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from'])) {
            $query->where('start_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('start_at', '<=', $filters['to']);
        }

        if (isset($filters['client_id'])) {
            $query->whereHas('participants', function ($q) use ($filters): void {
                $q->where('client_id', $filters['client_id']);
            });
        }

        $query->orderBy('start_at', 'desc');

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function create(User $trainer, array $data): TrainingSession
    {
        if (isset($data['idempotency_key'])) {
            $existing = TrainingSession::where('idempotency_key', $data['idempotency_key'])
                ->where('trainer_id', $trainer->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $clientIds = $data['client_ids'] ?? [];
        unset($data['client_ids']);

        $data['trainer_id'] = $trainer->id;
        $data['status'] = $data['status'] ?? 'planned';

        $session = TrainingSession::create($data);

        foreach ($clientIds as $clientId) {
            SessionParticipant::create([
                'session_id' => $session->id,
                'client_id'  => $clientId,
            ]);
        }

        $session->load('participants');

        SessionCreated::dispatch($session);

        return $session;
    }

    public function show(TrainingSession $session): TrainingSession
    {
        return $session;
    }

    public function update(TrainingSession $session, array $data): TrainingSession
    {
        $changes = [];

        if (isset($data['client_ids'])) {
            $clientIds = $data['client_ids'];
            unset($data['client_ids']);

            $session->participants()->delete();

            foreach ($clientIds as $clientId) {
                SessionParticipant::create([
                    'session_id' => $session->id,
                    'client_id'  => $clientId,
                ]);
            }

            $changes['client_ids'] = $clientIds;
        }

        $originalAttributes = $session->getAttributes();
        $session->update($data);
        $session->refresh();

        foreach ($data as $key => $value) {
            if (($originalAttributes[$key] ?? null) !== $value) {
                $changes[$key] = $value;
            }
        }

        if (!empty($changes)) {
            SessionUpdated::dispatch($session, $changes);
        }

        return $session;
    }

    public function updateStatus(TrainingSession $session, string $newStatus, ?string $reason = null): TrainingSession
    {
        $oldStatus = $session->status;

        $session->update([
            'status'              => $newStatus,
            'status_changed_at'   => now(),
            'cancellation_reason' => $reason,
        ]);

        $session->refresh();

        SessionStatusChanged::dispatch($session, $oldStatus, $newStatus, auth()->user());

        return $session;
    }

    public function cancel(TrainingSession $session, string $reason): TrainingSession
    {
        return $this->updateStatus($session, 'canceled', $reason);
    }

    public function delete(TrainingSession $session): void
    {
        $session->participants()->delete();
        $session->delete();
    }

    public function createSeries(User $trainer, array $data): SessionSeries
    {
        $series = SessionSeries::create([
            'trainer_id'      => $trainer->id,
            'template'        => [
                'title'            => $data['title'],
                'type'             => $data['type'] ?? null,
                'duration_minutes' => $data['duration_minutes'],
                'client_ids'       => $data['client_ids'] ?? [],
                'program_id'       => $data['program_id'] ?? null,
            ],
            'recurrence_rule' => $data['recurrence_rule'],
            'timezone'        => $data['timezone'],
        ]);

        $this->materializeSessions($series);

        return $series->load('sessions');
    }

    public function getSchedule(User $user, string $from, string $to): Collection
    {
        $query = TrainingSession::between($from, $to);

        if ($user->isTrainer()) {
            $query->forTrainer($user->id);
        } else {
            $query->whereHas('participants', function ($q) use ($user): void {
                $q->whereHas('client', function ($cq) use ($user): void {
                    $cq->where('user_id', $user->id);
                });
            });
        }

        return $query->with('participants')->orderBy('start_at')->get();
    }

    /**
     * Materialize sessions from a series template and recurrence rule.
     */
    private function materializeSessions(SessionSeries $series): void
    {
        $template = $series->template;
        $rule = $series->recurrence_rule;
        $timezone = $series->timezone;

        $daysOfWeek = $rule['days_of_week'] ?? [];
        $time = $rule['time'] ?? '09:00';
        $untilDate = Carbon::parse($rule['until_date'], $timezone);
        $durationMinutes = $template['duration_minutes'] ?? 60;
        $clientIds = $template['client_ids'] ?? [];

        $current = Carbon::now($timezone)->startOfDay();
        if ($current->lt(Carbon::today($timezone))) {
            $current = Carbon::today($timezone);
        }

        $dayMap = ['monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 0];

        while ($current->lte($untilDate)) {
            $dayName = strtolower($current->format('l'));

            if (in_array($dayName, $daysOfWeek)) {
                $startAt = $current->copy()->setTimeFromTimeString($time);
                $endAt = $startAt->copy()->addMinutes($durationMinutes);

                $session = TrainingSession::create([
                    'trainer_id' => $series->trainer_id,
                    'title'      => $template['title'],
                    'type'       => $template['type'] ?? null,
                    'start_at'   => $startAt,
                    'end_at'     => $endAt,
                    'status'     => 'planned',
                    'series_id'  => $series->id,
                    'program_id' => $template['program_id'] ?? null,
                ]);

                foreach ($clientIds as $clientId) {
                    SessionParticipant::create([
                        'session_id' => $session->id,
                        'client_id'  => $clientId,
                    ]);
                }
            }

            $current->addDay();
        }

        $series->update(['materialized_until' => $untilDate]);
    }
}
