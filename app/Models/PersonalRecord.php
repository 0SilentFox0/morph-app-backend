<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalRecord extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'personal_records';

    protected $fillable = [
        'client_id',
        'exercise_id',
        'weight_kg',
        'reps',
        'achieved_at',
        'workout_log_set_id',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'achieved_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function workoutLogSet(): BelongsTo
    {
        return $this->belongsTo(WorkoutLogSet::class);
    }

    // ── Accessors ─────────────────────────────────────────────────

    /**
     * Estimated 1RM using the Epley formula: weight * (1 + reps / 30)
     */
    public function getEstimated1rmAttribute(): float
    {
        if ($this->reps <= 0) {
            return (float) $this->weight_kg;
        }

        return round((float) $this->weight_kg * (1 + $this->reps / 30), 2);
    }
}
