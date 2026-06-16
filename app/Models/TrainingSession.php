<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrainingSession extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'training_sessions';

    protected $fillable = [
        'trainer_id',
        'title',
        'type',
        'start_at',
        'end_at',
        'status',
        'status_changed_at',
        'cancellation_reason',
        'notes',
        'program_id',
        'client_package_id',
        'series_id',
        'series_overridden',
        'google_event_id',
        'google_event_etag',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'start_at'          => 'datetime',
            'end_at'            => 'datetime',
            'status_changed_at' => 'datetime',
            'series_overridden' => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function clientPackage(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(SessionSeries::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'session_id');
    }

    public function workoutLog(): HasOne
    {
        return $this->hasOne(WorkoutLog::class, 'session_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTrainer(Builder $query, string $id): Builder
    {
        return $query->where('trainer_id', $id);
    }

    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', 'planned');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_at', '>=', now());
    }

    public function scopeBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('start_at', [$from, $to]);
    }
}
