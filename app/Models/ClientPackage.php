<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPackage extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'client_packages';

    protected $fillable = [
        'client_id',
        'trainer_id',
        'template_id',
        'kind',
        'sessions_count',
        'remaining_sessions',
        'validity_days',
        'expires_at',
        'price',
        'currency',
        'status',
        'assigned_at',
        'archived_at',
        'auto_renew',
        'auto_renewed_to_id',
        'expiry_reminded_at',
        'debt_since',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'expires_at' => 'datetime',
            'assigned_at' => 'datetime',
            'archived_at' => 'datetime',
            'debt_since' => 'datetime',
            'expiry_reminded_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PackageTemplate::class, 'template_id');
    }

    public function renewedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'auto_renewed_to_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'client_package_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'client_package_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTrainer(Builder $query, string $trainerId): Builder
    {
        return $query->where('trainer_id', $trainerId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeWithDebt(Builder $query): Builder
    {
        return $query->whereNotNull('debt_since');
    }

    // ── Methods ───────────────────────────────────────────────────

    public function isExhausted(): bool
    {
        return $this->remaining_sessions !== null && $this->remaining_sessions <= 0;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function decrementSession(): self
    {
        if ($this->remaining_sessions !== null && $this->remaining_sessions > 0) {
            $this->decrement('remaining_sessions');
            $this->refresh();
        }

        return $this;
    }
}
