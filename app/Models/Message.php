<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'messages';

    protected $attributes = [
        'media_file_ids' => '[]',
    ];

    const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'media_file_ids',
        'client_message_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'media_file_ids' => 'array',
            'sent_at'        => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
