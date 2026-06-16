<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProgram extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'client_programs';

    const UPDATED_AT = null;

    protected $fillable = [
        'client_id',
        'program_id',
        'program_snapshot',
        'assigned_at',
        'removed_at',
    ];

    protected function casts(): array
    {
        return [
            'program_snapshot' => 'array',
            'assigned_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
