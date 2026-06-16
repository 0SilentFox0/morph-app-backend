<?php

namespace App\Services;

use App\Models\User;
use App\Models\TrainingSession;
use App\Events\WorkoutLogSetCreated;
use App\Events\WorkoutLogSetUpdated;
use App\Events\WorkoutLogUpdated;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use App\Models\WorkoutLogSet;
use Illuminate\Contracts\Pagination\CursorPaginator;

class WorkoutTrackingService
{
    public function startWorkout(TrainingSession $session, User $user): WorkoutLog
    {
        $log = WorkoutLog::create([
            'session_id'         => $session->id,
            'started_at'         => now(),
            'started_by_user_id' => $user->id,
            'last_version'       => 1,
        ]);

        // Populate exercises from program if session is linked to one
        if ($session->program_id) {
            $this->populateExercisesFromProgram($log, $session);
        }

        WorkoutLogUpdated::dispatch($log, $user->id, $log->last_version);

        return $log->load(['session', 'exercises.sets']);
    }

    public function finishWorkout(WorkoutLog $log, User $user): WorkoutLog
    {
        if ($log->finished_at) {
            return $log->load('session');
        }

        $log->update([
            'finished_at'         => now(),
            'finished_by_user_id' => $user->id,
        ]);

        return $log->refresh()->load('session');
    }

    public function getLog(WorkoutLog $log): WorkoutLog
    {
        return $log->load(['exercises.sets']);
    }

    public function addExercise(WorkoutLog $log, array $data): WorkoutLogExercise
    {
        $maxOrder = $log->exercises()->max('order') ?? 0;

        return WorkoutLogExercise::create([
            'workout_log_id'    => $log->id,
            'exercise_id'       => $data['exercise_id'],
            'order'             => $maxOrder + 1,
            'name_snapshot'     => $data['name_snapshot'],
            'planned_sets'      => $data['planned_sets'] ?? null,
            'planned_reps'      => $data['planned_reps'] ?? null,
            'planned_weight_kg' => $data['planned_weight_kg'] ?? null,
        ]);
    }

    public function logSet(WorkoutLog $log, WorkoutLogExercise $exercise, array $data, User $actor): WorkoutLogSet
    {
        // Idempotency check via client_uuid
        if (isset($data['client_uuid'])) {
            $existing = WorkoutLogSet::where('client_uuid', $data['client_uuid'])
                ->where('workout_log_id', $log->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $newVersion = ($log->last_version ?? 0) + 1;

        $set = WorkoutLogSet::create([
            'workout_log_id'          => $log->id,
            'workout_log_exercise_id' => $exercise->id,
            'exercise_id'             => $data['exercise_id'],
            'set_index'               => $data['set_index'],
            'reps'                    => $data['reps'],
            'weight_kg'               => $data['weight_kg'],
            'rest_seconds'            => $data['rest_seconds'] ?? null,
            'performed_at'            => now(),
            'actor_user_id'           => $actor->id,
            'is_pr'                   => $data['is_pr'] ?? false,
            'client_uuid'             => $data['client_uuid'] ?? null,
            'version'                 => $newVersion,
        ]);

        $log->update(['last_version' => $newVersion]);

        WorkoutLogSetCreated::dispatch($set, $actor->id, $newVersion);

        return $set;
    }

    public function updateSet(WorkoutLogSet $set, array $data, User $actor): WorkoutLogSet
    {
        $log = $set->workoutLog;
        $newVersion = ($log->last_version ?? 0) + 1;

        $set->update(array_filter([
            'reps'         => $data['reps'] ?? null,
            'weight_kg'    => $data['weight_kg'] ?? null,
            'rest_seconds' => $data['rest_seconds'] ?? null,
            'version'      => $newVersion,
        ], fn ($value) => $value !== null));

        $log->update(['last_version' => $newVersion]);

        $set->refresh();

        WorkoutLogSetUpdated::dispatch($set, $actor->id, $newVersion);

        return $set;
    }

    public function deleteSet(WorkoutLogSet $set): void
    {
        $set->delete();
    }

    public function getHistory(User $user, array $filters = []): CursorPaginator
    {
        $query = WorkoutLog::query();

        if ($user->isTrainer()) {
            $query->whereHas('session', function ($q) use ($user): void {
                $q->where('trainer_id', $user->id);
            });
        } else {
            $query->whereHas('session.participants', function ($q) use ($user): void {
                $q->whereHas('client', function ($cq) use ($user): void {
                    $cq->where('user_id', $user->id);
                });
            });
        }

        if (isset($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }

        $query->orderBy('started_at', 'desc');

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    /**
     * Populate workout log exercises from the session's linked program.
     */
    private function populateExercisesFromProgram(WorkoutLog $log, TrainingSession $session): void
    {
        // Stub: load exercises from the program and create WorkoutLogExercise entries
        // This would typically query the Program module for exercise definitions
        // $program = $session->program;
        // foreach ($program->exercises as $index => $exercise) {
        //     WorkoutLogExercise::create([...]);
        // }
    }
}
