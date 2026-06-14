<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BodyMeasurement extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'body_measurements';

    const UPDATED_AT = null;

    protected $fillable = [
        'client_id',
        'metric_type',
        'value',
        'unit',
        'measured_at',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'measured_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
