<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionParticipant extends Model
{
    use HasFactory;

    protected $table = 'session_participants';

    public $incrementing = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'client_id',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
