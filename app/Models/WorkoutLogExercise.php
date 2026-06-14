<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutLogExercise extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'workout_log_exercises';

    const UPDATED_AT = null;

    protected $fillable = [
        'workout_log_id',
        'exercise_id',
        'order',
        'name_snapshot',
        'planned_sets',
        'planned_reps',
        'planned_weight_kg',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function workoutLog(): BelongsTo
    {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Exercise');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutLogSet::class);
    }
}
