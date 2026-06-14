<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use HasFactory, HasUuids;

    const UPDATED_AT = null;

    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token_hash',
        'device_label',
        'ip',
        'user_agent',
        'expires_at',
        'revoked_at',
        'replaced_by_id',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'   => 'datetime',
            'revoked_at'   => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }
}
