<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WorkoutLog extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'workout_logs';

    protected $fillable = [
        'session_id',
        'started_at',
        'started_by_user_id',
        'finished_at',
        'finished_by_user_id',
        'last_version',
    ];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function finishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finished_by_user_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutLogExercise::class);
    }

    public function sets(): HasManyThrough
    {
        return $this->hasManyThrough(WorkoutLogSet::class, WorkoutLogExercise::class);
    }
}
