<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exercise;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ExerciseService
{
    public function list(User $trainer, array $filters = []): CursorPaginator
    {
        $query = Exercise::forTrainer($trainer->id);

        if (isset($filters['q'])) {
            $search = $filters['q'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['muscle_group'])) {
            $query->whereJsonContains('muscle_groups', $filters['muscle_group']);
        }

        if (!isset($filters['include_archived']) || !$filters['include_archived']) {
            $query->active();
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $allowedSorts = ['name', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function create(User $trainer, array $data): Exercise
    {
        $data['trainer_id'] = $trainer->id;

        return Exercise::create($data);
    }

    public function update(Exercise $exercise, array $data): Exercise
    {
        $exercise->update($data);

        return $exercise->refresh();
    }

    public function archive(Exercise $exercise): Exercise
    {
        $exercise->update([
            'archived_at' => now(),
        ]);

        return $exercise->refresh();
    }

    public function restore(Exercise $exercise): Exercise
    {
        $exercise->update([
            'archived_at' => null,
        ]);

        return $exercise->refresh();
    }
}
