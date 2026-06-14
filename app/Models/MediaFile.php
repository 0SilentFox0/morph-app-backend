<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'media_files';

    protected $fillable = [
        'owner_user_id',
        'purpose',
        'mime',
        'size_bytes',
        's3_bucket',
        's3_key',
        'original_name',
        'status',
        'thumbnails',
        'context',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'thumbnails'  => 'array',
            'context'     => 'array',
            'uploaded_at' => 'datetime',
            'size_bytes'  => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
