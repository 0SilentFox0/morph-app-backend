<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageTemplate extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'package_templates';

    protected $fillable = [
        'trainer_id',
        'name',
        'kind',
        'sessions_count',
        'validity_days',
        'price',
        'currency',
        'auto_renew_default',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'auto_renew_default' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function clientPackages(): HasMany
    {
        return $this->hasMany(ClientPackage::class, 'template_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForTrainer(Builder $query, string $trainerId): Builder
    {
        return $query->where('trainer_id', $trainerId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }
}
