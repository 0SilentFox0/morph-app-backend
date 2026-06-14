<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'exercises';

    protected $attributes = [
        'muscle_groups' => '[]',
        'equipment' => '[]',
    ];

    protected $fillable = [
        'trainer_id',
        'name',
        'description',
        'muscle_groups',
        'equipment',
        'video_file_id',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'muscle_groups' => 'array',
            'equipment' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function videoFile(): BelongsTo
    {
        return $this->belongsTo('App\Models\MediaFile', 'video_file_id');
    }

    public function programExercises(): HasMany
    {
        return $this->hasMany(ProgramExercise::class);
    }

    public function scopeForTrainer(Builder $query, string $trainerId): Builder
    {
        return $query->where('trainer_id', $trainerId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }
}
