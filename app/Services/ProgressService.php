<?php

namespace App\Services;

use App\Models\User;
use App\Models\BodyMeasurement;
use App\Models\PersonalRecord;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class ProgressService
{
    public function listMeasurements(string $clientId, array $filters = []): CursorPaginator
    {
        $query = BodyMeasurement::where('client_id', $clientId);

        if (isset($filters['metric_type'])) {
            $query->where('metric_type', $filters['metric_type']);
        }

        if (isset($filters['from'])) {
            $query->where('measured_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('measured_at', '<=', $filters['to']);
        }

        $query->orderBy('measured_at', 'desc');

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function createMeasurement(array $data, User $recordedBy): BodyMeasurement
    {
        $data['recorded_by_user_id'] = $recordedBy->id;

        return BodyMeasurement::create($data);
    }

    public function deleteMeasurement(BodyMeasurement $measurement): void
    {
        $measurement->delete();
    }

    public function getMeasurementHistory(string $clientId, string $metricType): Collection
    {
        return BodyMeasurement::where('client_id', $clientId)
            ->where('metric_type', $metricType)
            ->orderBy('measured_at', 'asc')
            ->get();
    }

    public function listPersonalRecords(string $clientId): Collection
    {
        return PersonalRecord::where('client_id', $clientId)
            ->with('exercise')
            ->orderBy('achieved_at', 'desc')
            ->get();
    }

    public function checkAndUpdatePR(
        string $clientId,
        string $exerciseId,
        float $weightKg,
        int $reps,
        string $achievedAt,
        ?string $setId = null,
    ): ?PersonalRecord {
        $estimated1rm = $reps > 0
            ? round($weightKg * (1 + $reps / 30), 2)
            : $weightKg;

        $existingPR = PersonalRecord::where('client_id', $clientId)
            ->where('exercise_id', $exerciseId)
            ->first();

        if ($existingPR) {
            $existingEstimated1rm = $existingPR->estimated_1rm;

            if ($estimated1rm <= $existingEstimated1rm) {
                return null;
            }

            $existingPR->update([
                'weight_kg' => $weightKg,
                'reps' => $reps,
                'achieved_at' => $achievedAt,
                'workout_log_set_id' => $setId,
            ]);

            return $existingPR->refresh();
        }

        return PersonalRecord::create([
            'client_id' => $clientId,
            'exercise_id' => $exerciseId,
            'weight_kg' => $weightKg,
            'reps' => $reps,
            'achieved_at' => $achievedAt,
            'workout_log_set_id' => $setId,
        ]);
    }
}
