<?php

namespace App\Services;

use App\Models\User;
use App\Events\AccountDeletionRequested;
use App\Events\PasswordChanged;
use App\Events\UserRegistered;
use App\Models\EmailChangeRequest;
use App\Models\EmailVerification;
use App\Models\PasswordReset;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_at: \DateTimeInterface}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => $data['password'],
            'role'          => $data['role'],
        ]);

        // Create email verification token
        $verificationToken = Str::random(64);
        EmailVerification::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $verificationToken),
            'expires_at' => now()->addHours(24),
        ]);

        UserRegistered::dispatch($user);

        return $this->createTokenPair($user, $data['device_label'] ?? null);
    }

    /**
     * Authenticate a user with email and password.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_at: \DateTimeInterface}
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, ?string $deviceLabel = null): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->createTokenPair($user, $deviceLabel);
    }

    /**
     * Rotate a refresh token: revoke old, create new pair.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_at: \DateTimeInterface}
     *
     * @throws ValidationException
     */
    public function refreshToken(string $refreshToken): array
    {
        $tokenHash = hash('sha256', $refreshToken);

        $existingToken = RefreshToken::where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $existingToken) {
            throw ValidationException::withMessages([
                'refresh_token' => ['The refresh token is invalid or has expired.'],
            ]);
        }

        $user = $existingToken->user;

        // Revoke old refresh token
        $existingToken->update(['revoked_at' => now()]);

        // Create new token pair
        $result = $this->createTokenPair($user, $existingToken->device_label);

        // Link old token to new one
        $newRefreshToken = RefreshToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->latest()
            ->first();

        $existingToken->update(['replaced_by_id' => $newRefreshToken?->id]);

        return $result;
    }

    /**
     * Revoke the current access token.
     */
    public function logout(User $user, string $tokenId): void
    {
        $user->tokens()->where('id', $tokenId)->delete();

        // Revoke associated refresh tokens
        RefreshToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Revoke all tokens for a user.
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();

        RefreshToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Initiate the forgot-password flow.
     */
    public function forgotPassword(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Silently return to prevent email enumeration
            return;
        }

        // Invalidate previous reset tokens
        PasswordReset::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $token = Str::random(64);

        PasswordReset::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
        ]);

        // TODO: Dispatch notification/mail event with $token
    }

    /**
     * Reset the user's password using a valid token.
     *
     * @throws ValidationException
     */
    public function resetPassword(string $token, string $password): void
    {
        $tokenHash = hash('sha256', $token);

        $resetRecord = PasswordReset::where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $resetRecord) {
            throw ValidationException::withMessages([
                'token' => ['The password reset token is invalid or has expired.'],
            ]);
        }

        $user = $resetRecord->user;
        $user->update(['password_hash' => $password]);

        $resetRecord->update(['used_at' => now()]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();
        RefreshToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        PasswordChanged::dispatch($user);
    }

    /**
     * Verify a user's email address.
     *
     * @throws ValidationException
     */
    public function verifyEmail(string $token): void
    {
        $tokenHash = hash('sha256', $token);

        $verification = EmailVerification::where('token_hash', $tokenHash)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'token' => ['The email verification token is invalid or has expired.'],
            ]);
        }

        $verification->update(['verified_at' => now()]);

        $verification->user->update(['email_verified_at' => now()]);
    }

    /**
     * Request an email change for the authenticated user.
     */
    public function changeEmail(User $user, string $newEmail): void
    {
        // Invalidate previous change requests
        EmailChangeRequest::where('user_id', $user->id)
            ->whereNull('confirmed_at')
            ->update(['confirmed_at' => now()]);

        $token = Str::random(64);

        EmailChangeRequest::create([
            'user_id'    => $user->id,
            'new_email'  => $newEmail,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
        ]);

        // TODO: Dispatch notification/mail event with $token to new email
    }

    /**
     * Confirm an email change using the token.
     *
     * @throws ValidationException
     */
    public function confirmEmailChange(string $token): void
    {
        $tokenHash = hash('sha256', $token);

        $changeRequest = EmailChangeRequest::where('token_hash', $tokenHash)
            ->whereNull('confirmed_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $changeRequest) {
            throw ValidationException::withMessages([
                'token' => ['The email change token is invalid or has expired.'],
            ]);
        }

        $changeRequest->user->update(['email' => $changeRequest->new_email]);
        $changeRequest->update(['confirmed_at' => now()]);
    }

    /**
     * Soft-delete the user account and schedule permanent deletion.
     */
    public function deleteAccount(User $user): void
    {
        $user->update(['deletion_scheduled_at' => now()->addDays(30)]);
        $user->delete();

        // Revoke all tokens
        $user->tokens()->delete();
        RefreshToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        AccountDeletionRequested::dispatch($user);
    }

    // ── Private ───────────────────────────────────────────────────

    /**
     * Create an access token (Sanctum) and a refresh token pair.
     *
     * @return array{user: User, access_token: string, refresh_token: string, expires_at: \DateTimeInterface}
     */
    private function createTokenPair(User $user, ?string $deviceLabel = null): array
    {
//        $expiresAt = now()->addMinutes(config('sanctum.expiration', 60));
        $expiresAt = now()->addWeek();

        $accessToken = $user->createToken(
            name: $deviceLabel ?? 'api',
            expiresAt: $expiresAt,
        );

        $rawRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $rawRefreshToken),
            'device_label' => $deviceLabel,
            'ip'           => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
            'expires_at'   => now()->addDays(30),
        ]);

        return [
            'user'          => $user,
            'access_token'  => $accessToken->plainTextToken,
            'refresh_token' => $rawRefreshToken,
            'expires_at'    => $expiresAt,
        ];
    }
}
