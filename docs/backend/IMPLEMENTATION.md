# Implementation Overview

> Опис реалізованої архітектури бекенду. Актуальний стан кодової бази після початкового scaffolding.

**Стек:** PHP 8.4 · Laravel 13 · Sanctum 4.3 · MySQL 8+

---

## 1. Architecture — Standard Laravel

Стандартна структура Laravel без модулів. Весь код живе в стандартних директоріях:

```
app/
├── Events/               ← domain events (ShouldBroadcast де потрібно)
├── Http/
│   ├── Controllers/      ← thin, delegates to services
│   ├── Requests/         ← Form Request validation
│   └── Resources/        ← API resource transformers
├── Jobs/                 ← queued tasks
├── Listeners/            ← event reactions
├── Models/               ← Eloquent models (all in one namespace)
├── Policies/             ← authorization
├── Providers/
│   └── AppServiceProvider.php ← policy registration
└── Services/             ← business logic

routes/
├── api.php               ← all API routes (88 endpoints)
├── web.php
└── console.php

database/migrations/
├── 0001_01_01_000000_create_users_table.php          ← rewritten
├── 0001_01_01_000001_create_cache_table.php           ← Laravel default
├── 0001_01_01_000002_create_jobs_table.php             ← Laravel default
├── 0002_01_01_000000_create_auth_tables.php            ← 7 tables
├── 0002_01_01_000001_create_media_files_table.php
├── 0002_01_01_000002_create_notifications_tables.php   ← 2 tables
├── 0002_01_01_000003_create_onboarding_progress_table.php
├── 0002_01_01_000004_create_clients_tables.php         ← 2 tables
├── 0002_01_01_000005_create_exercises_table.php
├── 0002_01_01_000006_create_programs_tables.php        ← 5 tables
├── 0002_01_01_000007_create_sessions_tables.php        ← 3 tables
├── 0002_01_01_000008_create_chat_tables.php             ← 3 tables
├── 0002_01_01_000009_create_workout_tables.php          ← 3 tables
├── 0002_01_01_000010_create_packages_tables.php         ← 2 tables
├── 0002_01_01_000011_create_transactions_tables.php     ← 2 tables
├── 0002_01_01_000012_create_progress_tables.php         ← 2 tables
├── 0002_01_01_000013_create_calendar_integrations_table.php
└── 0002_01_01_000014_create_analytics_tables.php        ← 3 tables
```

---

## 2. Route Registration

All API routes defined in `routes/api.php`, loaded in `bootstrap/app.php`:

```php
->withRouting(
    // ...
    then: function (): void {
        Route::middleware('api')->group(base_path('routes/api.php'));
    },
)
```

Routes are grouped by feature area within `api.php`. All authenticated routes use `auth:sanctum` middleware with `/v1/` prefix.

---

## 3. Database Schema

**37 таблиць** across 18 migration files. Повна відповідність до [`DB_STRUCTURE.md`](DB_STRUCTURE.md).

Ключові рішення:
- **Training sessions** → таблиця `training_sessions` (не `sessions`) щоб уникнути конфлікту з Laravel framework sessions.
- **UUID primary keys** скрізь (HasUuids trait, `Str::orderedUuid()`).
- **Composite primary keys** для pivot-таблиць: `session_participants`, `conversation_participants`, `program_likes`.
- **Soft deletes** (`deleted_at`): users, media_files, device_tokens, conversation_participants, messages, workout_log_sets, transactions, withdrawals, body_measurements, calendar_integrations, session_series.
- **Deferred foreign keys** для circular references (conversations ↔ messages, training_sessions → client_packages).
- **JSON columns** для flexible data: notification_preferences, certifications, training_types, tags, program_snapshot, recurrence_rule, etc.

---

## 4. Key Components

### User Model (`app/Models/User.php`)
- Extends `Authenticatable` з traits: `HasApiTokens`, `HasFactory`, `HasUuids`, `Notifiable`, `SoftDeletes`.
- Custom password column: `password_hash` (override `getAuthPasswordName()`).
- Role helpers: `isTrainer()`, `isClient()`, `isAdmin()`.
- Relationships to all related models directly (same namespace).

### MediaFile Model (`app/Models/MediaFile.php`)
- Shared model для всіх file uploads (avatars, exercise videos, chat media, exports).
- Purpose enum: `avatar`, `exercise_video`, `chat_media`, `data_export`, `progress_export`, `other`.

### FileService (`app/Services/FileService.php`)
- `initiateUpload()` — create pending MediaFile + generate signed upload URL.
- `completeUpload()` — mark as ready.
- `getSignedUrl()` — generate time-limited access URL.
- `deleteFile()` — soft-delete record + schedule S3 cleanup.

### Policy Registration (`app/Providers/AppServiceProvider.php`)
Усі policies зареєстровані через `Gate::policy()`:
Client, Exercise, Program, PackageTemplate, ClientPackage, Transaction, Withdrawal, BodyMeasurement, TrainingSession, Conversation, Message, WorkoutLog.

---

## 5. API Endpoints

**88 endpoints**. Усі під prefix `/v1/`.

### Auth (11 endpoints)

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/v1/auth/register` | — | Register new user |
| POST | `/v1/auth/login` | — | Login with email/password |
| POST | `/v1/auth/refresh` | — | Rotate refresh token |
| POST | `/v1/auth/forgot-password` | — | Initiate password reset |
| POST | `/v1/auth/reset-password` | — | Reset password with token |
| POST | `/v1/auth/verify-email` | — | Verify email with token |
| POST | `/v1/auth/confirm-email-change` | — | Confirm email change |
| POST | `/v1/auth/logout` | Bearer | Revoke current token |
| POST | `/v1/auth/logout-all` | Bearer | Revoke all tokens |
| POST | `/v1/auth/change-email` | Bearer | Request email change |
| DELETE | `/v1/auth/me/account` | Bearer | Delete account (30d grace) |

### Users (7 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/me` | Own profile |
| PUT | `/v1/me` | Update profile |
| PUT | `/v1/me/settings` | Update settings (timezone, locale, currency) |
| PUT | `/v1/me/avatar` | Update avatar |
| GET | `/v1/me/onboarding` | Onboarding progress |
| PUT | `/v1/me/onboarding/{step}` | Update onboarding step |
| GET | `/v1/users/{id}` | Public profile |

### Clients (9 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/clients` | List trainer's clients |
| POST | `/v1/clients` | Create client |
| GET | `/v1/clients/{client}` | Show client |
| PUT | `/v1/clients/{client}` | Update client |
| POST | `/v1/clients/{client}/archive` | Archive client |
| POST | `/v1/clients/{client}/restore` | Restore client |
| POST | `/v1/clients/{client}/invite` | Send invitation |
| POST | `/v1/client-invitations/{code}/accept` | Accept invitation |
| DELETE | `/v1/client-invitations/{invitation}` | Revoke invitation |

### Programs & Exercises (15 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/exercises` | List exercises |
| POST | `/v1/exercises` | Create exercise |
| GET | `/v1/exercises/{exercise}` | Show exercise |
| PUT | `/v1/exercises/{exercise}` | Update exercise |
| POST | `/v1/exercises/{exercise}/archive` | Archive exercise |
| POST | `/v1/exercises/{exercise}/restore` | Restore exercise |
| GET | `/v1/programs` | List programs |
| POST | `/v1/programs` | Create program |
| GET | `/v1/programs/{program}` | Show program (increments views) |
| PUT | `/v1/programs/{program}` | Update program |
| POST | `/v1/programs/{program}/archive` | Archive program |
| POST | `/v1/programs/{program}/like` | Toggle like |
| PUT | `/v1/programs/{program}/exercises` | Update program exercises |
| POST | `/v1/programs/{program}/assign` | Assign program to client |
| DELETE | `/v1/client-programs/{clientProgram}` | Remove assignment |

### Sessions (9 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/sessions` | List sessions |
| POST | `/v1/sessions` | Create session |
| GET | `/v1/sessions/{session}` | Show session |
| PUT | `/v1/sessions/{session}` | Update session |
| POST | `/v1/sessions/{session}/status` | Change status |
| POST | `/v1/sessions/{session}/cancel` | Cancel session |
| DELETE | `/v1/sessions/{session}` | Delete session |
| GET | `/v1/sessions/schedule` | Schedule view (date range) |
| POST | `/v1/session-series` | Create recurring series |

### Chat (6 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/conversations` | List conversations |
| POST | `/v1/conversations` | Create/get conversation |
| GET | `/v1/conversations/{conversation}/messages` | List messages |
| POST | `/v1/conversations/{conversation}/messages` | Send message |
| POST | `/v1/conversations/{conversation}/read` | Mark as read |
| DELETE | `/v1/messages/{message}` | Delete message |

### Workout Tracking (8 endpoints)

| Method | Path | Description |
|---|---|---|
| POST | `/v1/sessions/{session}/workout` | Start workout |
| POST | `/v1/workout-logs/{log}/finish` | Finish workout |
| GET | `/v1/workout-logs/{log}` | Get workout log |
| POST | `/v1/workout-logs/{log}/exercises` | Add exercise to log |
| POST | `/v1/workout-logs/{log}/sets` | Log a set |
| PUT | `/v1/workout-log-sets/{set}` | Update a set |
| DELETE | `/v1/workout-log-sets/{set}` | Delete a set |
| GET | `/v1/workout-logs` | Workout history |

### Packages (9 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/package-templates` | List templates |
| POST | `/v1/package-templates` | Create template |
| GET | `/v1/package-templates/{template}` | Show template |
| PUT | `/v1/package-templates/{template}` | Update template |
| POST | `/v1/package-templates/{template}/archive` | Archive template |
| GET | `/v1/client-packages` | List client packages |
| POST | `/v1/client-packages` | Assign package to client |
| GET | `/v1/client-packages/{package}` | Show client package |
| POST | `/v1/client-packages/{package}/archive` | Archive client package |

### Transactions (8 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/transactions` | List transactions |
| POST | `/v1/transactions` | Create transaction |
| GET | `/v1/transactions/{transaction}` | Show transaction |
| PUT | `/v1/transactions/{transaction}` | Update transaction |
| DELETE | `/v1/transactions/{transaction}` | Delete transaction |
| GET | `/v1/withdrawals` | List withdrawals |
| POST | `/v1/withdrawals` | Create withdrawal |
| DELETE | `/v1/withdrawals/{withdrawal}` | Delete withdrawal |

### Progress (5 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/clients/{client}/measurements` | List measurements |
| POST | `/v1/clients/{client}/measurements` | Create measurement |
| GET | `/v1/clients/{client}/measurements/history` | Chart data |
| DELETE | `/v1/measurements/{measurement}` | Delete measurement |
| GET | `/v1/clients/{client}/personal-records` | List PRs |

### Notifications (6 endpoints)

| Method | Path | Description |
|---|---|---|
| GET | `/v1/notifications` | List notifications |
| POST | `/v1/notifications/{id}/read` | Mark as read |
| POST | `/v1/notifications/read-all` | Mark all as read |
| GET | `/v1/notifications/unread-count` | Unread count |
| POST | `/v1/device-tokens` | Register device token |
| DELETE | `/v1/device-tokens/{token}` | Remove device token |

---

## 6. Models Summary

**43 Eloquent models** total (all in `App\Models`):

| Area | Models |
|---|---|
| Auth & Identity | User, RefreshToken, OAuthIdentity, EmailVerification, PasswordReset, EmailChangeRequest, AuditLog, DataExport |
| Files | MediaFile |
| Onboarding | OnboardingProgress |
| Notifications | Notification, DeviceToken |
| Clients | Client, ClientInvitation |
| Programs & Exercises | Exercise, Program, ProgramExercise, ProgramVideo, ProgramLike, ClientProgram |
| Sessions | TrainingSession, SessionParticipant, SessionSeries, CalendarIntegration |
| Chat | Conversation, ConversationParticipant, Message |
| Workout Tracking | WorkoutLog, WorkoutLogExercise, WorkoutLogSet |
| Packages | PackageTemplate, ClientPackage |
| Transactions | Transaction, Withdrawal |
| Progress | BodyMeasurement, PersonalRecord |

---

## 7. Events & Broadcasting

Domain events for cross-concern communication and real-time sync:

| Event | Broadcasts to | Trigger |
|---|---|---|
| UserRegistered | — | Registration |
| PasswordChanged | — | Password reset |
| AccountDeletionRequested | — | Account deletion |
| ClientCreated | — | New client |
| ClientInvitationSent | — | Client invited |
| ProgramAssigned | — | Program assigned to client |
| SessionCreated | `private-user.{userId}` | Session created |
| SessionUpdated | `private-session.{id}` | Session modified |
| SessionStatusChanged | `private-session.{id}` | Status transition |
| MessageSent | `private-conversation.{id}` | New message |
| MessageRead | `private-conversation.{id}` | Read receipt |
| UserTyping | `private-conversation.{id}` | Typing indicator |
| WorkoutLogUpdated | `private-session.{sessionId}` | Log modified |
| WorkoutLogSetCreated | `private-session.{sessionId}` | Set logged |
| WorkoutLogSetUpdated | `private-session.{sessionId}` | Set modified |
| PackageAssigned | `private-user.{clientUserId}` | Package assigned |
| PackageExhausted | trainer + client channels | Sessions depleted |
| TransactionCreated | `private-user.{trainerId}` | Payment recorded |

---

## 8. Scheduled Jobs (stubs)

| Job | Schedule | Purpose |
|---|---|---|
| SessionRemindersJob | every 5 min | Push 24h/1h before session |
| PackageExpirationJob | daily 03:00 | Check expiring packages |
| SubscriptionRenewalJob | daily 00:30 | Auto-renew packages |

---

## 9. What's NOT Implemented Yet

Scaffolding створює структуру і основну логіку. Потребує доробки:

1. **Reverb broadcasting setup** — events мають `ShouldBroadcast`, але Reverb сервер не налаштований.
2. **FCM push delivery** — NotificationService створює in-app notifications, але FCM dispatch — stub.
3. **Calendar sync** — SyncCalendarOnSessionChange listener є stub. Потрібна Google Calendar API integration.
4. **OAuth (Socialite)** — OAuthIdentity модель є, але Socialite controllers/routes не створені.
5. **Email delivery** — password reset, email verification, invitations — token'и генеруються, але mail dispatch — TODO.
6. **File upload pipeline** — FileService має stubs, потрібна реальна S3 integration.
7. **Analytics** — таблиці створені (profile_view_events, analytics_cache, achievements), контролери не створені.
8. **Filament admin panel** — не встановлено.
9. **Rate limiting middleware** — не налаштовано (auth: 10/min, general: 120/min).
10. **Idempotency middleware** — endpoints приймають keys, але Redis caching layer не реалізований.
11. **RFC 7807 error handler** — потрібен custom exception handler в `bootstrap/app.php`.
12. **Tests** — жодних тестів поки немає.

---

## 10. File Counts

| Category | Files |
|---|---|
| Migrations | 18 |
| Models | 43 |
| Controllers | 16 |
| Services | 12 |
| Requests | 31 |
| Resources | 18 |
| Events | 18 |
| Listeners | 1 |
| Jobs | 3 |
| Policies | 12 |
| Routes | 1 (api.php) |
| **Total PHP** | **~173** |
