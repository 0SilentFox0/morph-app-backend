<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramExercise extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'program_exercises';

    const UPDATED_AT = null;

    protected $fillable = [
        'program_id',
        'exercise_id',
        'order',
        'sets',
        'reps',
        'weight_kg',
        'rest_seconds',
        'notes',
        'name_snapshot',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
