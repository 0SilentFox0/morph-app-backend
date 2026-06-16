<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $attributes = [
        'notification_preferences' => '{}',
    ];

    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'avatar_url',
        'role',
        'timezone',
        'locale',
        'currency',
        'notification_preferences',
        'points',
        'experience',
        'certifications',
        'training_types',
        'client_types',
        'locations',
        'work_schedule_start',
        'work_schedule_end',
        'work_schedule_days',
        'goals',
        'fitness_level',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'password_hash'            => 'hashed',
            'notification_preferences' => 'array',
            'certifications'           => 'array',
            'training_types'           => 'array',
            'client_types'             => 'array',
            'locations'                => 'array',
            'work_schedule_days'       => 'array',
            'goals'                    => 'array',
            'points'                   => 'integer',
            'email_verified_at'        => 'datetime',
            'onboarding_completed_at'  => 'datetime',
            'last_seen_at'             => 'datetime',
            'deletion_scheduled_at'    => 'datetime',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    // ── Relationships ─────────────────────────────────────────────

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function oauthIdentities(): HasMany
    {
        return $this->hasMany(OAuthIdentity::class);
    }

    public function trainerClients(): HasMany
    {
        return $this->hasMany(Client::class, 'trainer_id');
    }

    public function clientRecord(): HasMany
    {
        return $this->hasMany(Client::class, 'user_id');
    }

    public function onboardingProgress(): HasOne
    {
        return $this->hasOne(OnboardingProgress::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_user_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'trainer_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'trainer_id');
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'trainer_id');
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Conversation::class,
            'conversation_participants',
            'user_id',
            'conversation_id',
        );
    }

    public function calendarIntegrations(): HasMany
    {
        return $this->hasMany(CalendarIntegration::class);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isTrainer(): bool
    {
        return $this->role === 'trainer';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
