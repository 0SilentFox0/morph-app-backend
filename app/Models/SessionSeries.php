<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionSeries extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'session_series';

    const UPDATED_AT = null;

    protected $fillable = [
        'trainer_id',
        'template',
        'recurrence_rule',
        'timezone',
        'materialized_until',
    ];

    protected function casts(): array
    {
        return [
            'template'        => 'array',
            'recurrence_rule' => 'array',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'series_id');
    }
}
