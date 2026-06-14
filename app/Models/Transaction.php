<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'trainer_id',
        'client_id',
        'client_package_id',
        'amount',
        'currency',
        'method',
        'status',
        'paid_at',
        'note',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function clientPackage(): BelongsTo
    {
        return $this->belongsTo('App\Models\ClientPackage');
    }
}
