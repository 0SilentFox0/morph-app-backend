<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'withdrawals';

    protected $fillable = [
        'trainer_id',
        'amount',
        'currency',
        'withdrawn_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'withdrawn_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }
}
