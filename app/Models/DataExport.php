<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExport extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'data_exports';

    protected $fillable = [
        'user_id',
        'kind',
        'filters',
        'status',
        'size_bytes',
        'file_id',
        'signed_url',
        'signed_url_expires_at',
        'error',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters'               => 'array',
            'size_bytes'             => 'integer',
            'signed_url_expires_at' => 'datetime',
            'started_at'            => 'datetime',
            'completed_at'          => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
