<?php

namespace App\Services;

use App\Models\User;
use App\Models\Client;
use App\Events\ProgramAssigned;
use App\Models\ClientProgram;
use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramExercise;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class ProgramService
{
    public function list(User $trainer, array $filters = []): CursorPaginator
    {
        $query = Program::forTrainer($trainer->id);

        if (isset($filters['q'])) {
            $search = $filters['q'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (!isset($filters['include_archived']) || !$filters['include_archived']) {
            $query->active();
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $allowedSorts = ['name', 'created_at', 'views_count', 'likes_count'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        $query->with('coverFile');

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function create(User $trainer, array $data): Program
    {
        return DB::transaction(function () use ($trainer, $data): Program {
            $exercises = $data['exercises'] ?? [];
            unset($data['exercises']);

            $data['trainer_id'] = $trainer->id;
            $data['views_count'] = 0;
            $data['likes_count'] = 0;

            $program = Program::create($data);

            if (!empty($exercises)) {
                $this->syncExercises($program, $exercises);
            }

            return $program->load(['exercises.exercise', 'coverFile']);
        });
    }

    public function show(Program $program): Program
    {
        $program->increment('views_count');

        return $program->load(['exercises.exercise', 'coverFile']);
    }

    public function update(Program $program, array $data): Program
    {
        return DB::transaction(function () use ($program, $data): Program {
            $exercises = $data['exercises'] ?? null;
            unset($data['exercises']);

            $program->update($data);

            if ($exercises !== null) {
                $this->syncExercises($program, $exercises);
            }

            return $program->refresh()->load(['exercises.exercise', 'coverFile']);
        });
    }

    public function archive(Program $program): void
    {
        $program->update([
            'archived_at' => now(),
        ]);
    }

    public function like(Program $program, User $user): void
    {
        $exists = $program->likes()->where('user_id', $user->id)->exists();

        if ($exists) {
            $program->likes()->where('user_id', $user->id)->delete();
            $program->decrement('likes_count');
        } else {
            $program->likes()->create([
                'user_id' => $user->id,
            ]);
            $program->increment('likes_count');
        }
    }

    public function unlike(Program $program, User $user): void
    {
        $deleted = $program->likes()->where('user_id', $user->id)->delete();

        if ($deleted > 0) {
            $program->decrement('likes_count');
        }
    }

    public function assignToClient(Program $program, Client $client): ClientProgram
    {
        $snapshot = $program->load('exercises.exercise')->toArray();

        $clientProgram = ClientProgram::create([
            'client_id' => $client->id,
            'program_id' => $program->id,
            'program_snapshot' => $snapshot,
            'assigned_at' => now(),
        ]);

        ProgramAssigned::dispatch($clientProgram);

        return $clientProgram;
    }

    public function removeFromClient(ClientProgram $clientProgram): void
    {
        $clientProgram->update([
            'removed_at' => now(),
        ]);
    }

    public function updateExercises(Program $program, array $exercises): void
    {
        $this->syncExercises($program, $exercises);
    }

    private function syncExercises(Program $program, array $exercises): void
    {
        $program->exercises()->delete();

        foreach ($exercises as $exerciseData) {
            $exercise = Exercise::find($exerciseData['exercise_id']);

            ProgramExercise::create([
                'program_id' => $program->id,
                'exercise_id' => $exerciseData['exercise_id'],
                'order' => $exerciseData['order'],
                'sets' => $exerciseData['sets'],
                'reps' => $exerciseData['reps'],
                'weight_kg' => $exerciseData['weight_kg'] ?? null,
                'rest_seconds' => $exerciseData['rest_seconds'] ?? null,
                'notes' => $exerciseData['notes'] ?? null,
                'name_snapshot' => $exercise?->name,
            ]);
        }
    }
}
