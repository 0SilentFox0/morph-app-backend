<?php

namespace App\Services;

use App\Models\User;
use App\Models\DeviceToken;
use App\Models\Notification;

class NotificationService
{
    /**
     * Create and persist a notification for a user.
     */
    public function send(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?array $payload = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
    ): Notification {
        return Notification::create([
            'recipient_user_id' => $user->id,
            'type'              => $type,
            'title'             => $title,
            'body'              => $body,
            'payload'           => $payload,
            'source_type'       => $sourceType,
            'source_id'         => $sourceId,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }
    }

    /**
     * Mark all unread notifications as read for a user.
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get the count of unread notifications for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Register or update a device token for push notifications.
     */
    public function registerDeviceToken(
        User $user,
        string $token,
        string $platform,
        ?string $deviceLabel = null,
        ?string $appVersion = null,
    ): DeviceToken {
        return DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token'   => $token,
            ],
            [
                'platform'     => $platform,
                'device_label' => $deviceLabel,
                'app_version'  => $appVersion,
                'last_seen_at' => now(),
            ],
        );
    }

    /**
     * Remove a device token (soft-delete).
     */
    public function removeDeviceToken(string $token): void
    {
        DeviceToken::where('token', $token)->delete();
    }
}
