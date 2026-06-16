<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutLogSet extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'workout_log_sets';

    const UPDATED_AT = null;

    protected $fillable = [
        'workout_log_id',
        'workout_log_exercise_id',
        'exercise_id',
        'set_index',
        'reps',
        'weight_kg',
        'rest_seconds',
        'performed_at',
        'actor_user_id',
        'is_pr',
        'client_uuid',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'is_pr'        => 'boolean',
            'weight_kg'    => 'decimal:2',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function workoutLog(): BelongsTo
    {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function workoutLogExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutLogExercise::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
