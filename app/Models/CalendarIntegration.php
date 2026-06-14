<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarIntegration extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'calendar_integrations';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_email',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'calendar_id',
        'sync_token',
        'feed_token',
        'webhook_channel_id',
        'webhook_resource_id',
        'webhook_expires_at',
        'last_synced_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'access_token'       => 'encrypted',
            'refresh_token'      => 'encrypted',
            'token_expires_at'   => 'datetime',
            'webhook_expires_at' => 'datetime',
            'last_synced_at'     => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
