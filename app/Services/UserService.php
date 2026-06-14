<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\User;
use App\Models\OnboardingProgress;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    /**
     * Get the authenticated user's full profile with eager-loaded relations.
     */
    public function getProfile(User $user): User
    {
        return $user->load([
            'onboardingProgress',
        ]);
    }

    /**
     * Get a public profile for a given user (limited fields handled by Resource).
     *
     * @throws ModelNotFoundException
     */
    public function getPublicProfile(string $userId): User
    {
        return User::findOrFail($userId);
    }

    /**
     * Update the authenticated user's profile fields.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    /**
     * Update the authenticated user's settings.
     */
    public function updateSettings(User $user, array $data): User
    {
        $allowedSettings = [
            'timezone',
            'locale',
            'currency',
            'notification_preferences',
        ];

        $user->update(
            collect($data)->only($allowedSettings)->toArray(),
        );

        return $user->refresh();
    }

    /**
     * Update the authenticated user's avatar from a completed media file.
     */
    public function updateAvatar(User $user, string $mediaFileId): User
    {
        $mediaFile = MediaFile::findOrFail($mediaFileId);

        $user->update([
            'avatar_url' => $mediaFile->s3_key,
        ]);

        return $user->refresh();
    }

    /**
     * Get the onboarding progress for the user, creating a default if none exists.
     */
    public function getOnboardingProgress(User $user): OnboardingProgress
    {
        return OnboardingProgress::firstOrCreate(
            ['user_id' => $user->id],
            [
                'steps'        => [],
                'current_step' => 'welcome',
            ],
        );
    }

    /**
     * Update a specific onboarding step and advance the current step.
     */
    public function updateOnboardingStep(User $user, string $step, array $data): OnboardingProgress
    {
        $progress = $this->getOnboardingProgress($user);

        $steps = $progress->steps ?? [];
        $steps[$step] = array_merge($steps[$step] ?? [], $data, [
            'completed_at' => now()->toIso8601String(),
        ]);

        $progress->update([
            'steps'        => $steps,
            'current_step' => $step,
        ]);

        return $progress->refresh();
    }
}
