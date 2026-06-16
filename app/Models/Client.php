<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'clients';

    protected $attributes = [
        'tags' => '[]',
    ];

    protected $fillable = [
        'trainer_id',
        'user_id',
        'name',
        'email',
        'phone',
        'avatar_url',
        'type',
        'status',
        'notes',
        'tags',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ClientInvitation::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ClientPackage::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(ClientProgram::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(BodyMeasurement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeForTrainer(Builder $query, string $trainerId): Builder
    {
        return $query->where('trainer_id', $trainerId);
    }
}
