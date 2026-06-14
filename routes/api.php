<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BodyMeasurementController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPackageController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageTemplateController;
use App\Http\Controllers\PersonalRecordController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\WorkoutLogController;
use Illuminate\Support\Facades\Route;

// ── Auth (public) ────────────────────────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/confirm-email-change', [AuthController::class, 'confirmEmailChange']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/change-email', [AuthController::class, 'changeEmail']);
        Route::delete('/me/account', [AuthController::class, 'deleteAccount']);
    });
});

// ── All authenticated routes ─────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('v1')->group(function (): void {
    // Users & Profile
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/me', [UserController::class, 'updateProfile']);
    Route::put('/me/settings', [UserController::class, 'updateSettings']);
    Route::put('/me/avatar', [UserController::class, 'updateAvatar']);
    Route::get('/me/onboarding', [UserController::class, 'onboarding']);
    Route::put('/me/onboarding/{step}', [UserController::class, 'updateOnboardingStep']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Clients
    Route::apiResource('clients', ClientController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('clients/{client}/archive', [ClientController::class, 'archive']);
    Route::post('clients/{client}/restore', [ClientController::class, 'restore']);
    Route::post('clients/{client}/invite', [ClientController::class, 'invite']);
    Route::post('client-invitations/{code}/accept', [ClientController::class, 'acceptInvitation']);
    Route::delete('client-invitations/{invitation}', [ClientController::class, 'revokeInvitation']);

    // Exercises
    Route::apiResource('exercises', ExerciseController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('exercises/{exercise}/archive', [ExerciseController::class, 'archive']);
    Route::post('exercises/{exercise}/restore', [ExerciseController::class, 'restore']);

    // Programs
    Route::apiResource('programs', ProgramController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('programs/{program}/archive', [ProgramController::class, 'archive']);
    Route::post('programs/{program}/like', [ProgramController::class, 'like']);
    Route::put('programs/{program}/exercises', [ProgramController::class, 'updateExercises']);
    Route::post('programs/{program}/assign', [ProgramController::class, 'assign']);
    Route::delete('client-programs/{clientProgram}', [ProgramController::class, 'removeAssignment']);

    // Sessions
    Route::get('sessions/schedule', [SessionController::class, 'schedule']);
    Route::get('sessions', [SessionController::class, 'index']);
    Route::post('sessions', [SessionController::class, 'store']);
    Route::get('sessions/{session}', [SessionController::class, 'show']);
    Route::put('sessions/{session}', [SessionController::class, 'update']);
    Route::post('sessions/{session}/status', [SessionController::class, 'updateStatus']);
    Route::post('sessions/{session}/cancel', [SessionController::class, 'cancel']);
    Route::delete('sessions/{session}', [SessionController::class, 'destroy']);
    Route::post('session-series', [SessionController::class, 'storeSeries']);

    // Chat
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::post('conversations/{conversation}/read', [MessageController::class, 'markAsRead']);
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);

    // Workout Tracking
    Route::post('sessions/{session}/workout', [WorkoutLogController::class, 'start']);
    Route::post('workout-logs/{log}/finish', [WorkoutLogController::class, 'finish']);
    Route::get('workout-logs/{log}', [WorkoutLogController::class, 'show']);
    Route::post('workout-logs/{log}/exercises', [WorkoutLogController::class, 'addExercise']);
    Route::post('workout-logs/{log}/sets', [WorkoutLogController::class, 'logSet']);
    Route::put('workout-log-sets/{set}', [WorkoutLogController::class, 'updateSet']);
    Route::delete('workout-log-sets/{set}', [WorkoutLogController::class, 'deleteSet']);
    Route::get('workout-logs', [WorkoutLogController::class, 'history']);

    // Packages
    Route::apiResource('package-templates', PackageTemplateController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('package-templates/{template}/archive', [PackageTemplateController::class, 'archive']);
    Route::apiResource('client-packages', ClientPackageController::class)->only(['index', 'store', 'show']);
    Route::post('client-packages/{package}/archive', [ClientPackageController::class, 'archive']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('withdrawals', WithdrawalController::class)->only(['index', 'store', 'destroy']);

    // Progress
    Route::get('clients/{client}/measurements', [BodyMeasurementController::class, 'index']);
    Route::post('clients/{client}/measurements', [BodyMeasurementController::class, 'store']);
    Route::get('clients/{client}/measurements/history', [BodyMeasurementController::class, 'history']);
    Route::delete('measurements/{measurement}', [BodyMeasurementController::class, 'destroy']);
    Route::get('clients/{client}/personal-records', [PersonalRecordController::class, 'index']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/device-tokens', [NotificationController::class, 'registerDeviceToken']);
    Route::delete('/device-tokens/{token}', [NotificationController::class, 'removeDeviceToken']);
});
