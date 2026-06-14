<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'trainer_id',
        'name',
        'description',
        'difficulty',
        'estimated_duration_min',
        'cover_file_id',
        'views_count',
        'likes_count',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function coverFile(): BelongsTo
    {
        return $this->belongsTo('App\Models\MediaFile', 'cover_file_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(ProgramExercise::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProgramVideo::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ProgramLike::class);
    }

    public function clientPrograms(): HasMany
    {
        return $this->hasMany(ClientProgram::class);
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
