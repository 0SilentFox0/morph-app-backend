<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notifications';

    public const UPDATED_AT = null;

    protected $fillable = [
        'recipient_user_id',
        'type',
        'title',
        'body',
        'payload',
        'source_type',
        'source_id',
        'read_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'read_at'      => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
