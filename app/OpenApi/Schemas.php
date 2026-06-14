<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

// ── Auth Schemas ────────────────────────────────────────────────

#[OA\Schema(schema: 'TokenResponse', properties: [
    new OA\Property(property: 'access_token', type: 'string'),
    new OA\Property(property: 'refresh_token', type: 'string'),
    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
])]

// ── User Schemas ────────────────────────────────────────────────

#[OA\Schema(schema: 'User', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'email', type: 'string', format: 'email'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'avatar_url', type: 'string', nullable: true),
    new OA\Property(property: 'role', type: 'string', enum: ['client', 'trainer']),
    new OA\Property(property: 'timezone', type: 'string', nullable: true),
    new OA\Property(property: 'locale', type: 'string', nullable: true),
    new OA\Property(property: 'currency', type: 'string', nullable: true),
    new OA\Property(property: 'points', type: 'integer', nullable: true),
    new OA\Property(property: 'experience', type: 'string', nullable: true),
    new OA\Property(property: 'certifications', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'training_types', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'client_types', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'locations', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'work_schedule_start', type: 'string', format: 'time', nullable: true),
    new OA\Property(property: 'work_schedule_end', type: 'string', format: 'time', nullable: true),
    new OA\Property(property: 'work_schedule_days', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'goals', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'fitness_level', type: 'string', enum: ['beginner', 'intermediate', 'advanced', 'elite'], nullable: true),
    new OA\Property(property: 'onboarding_completed_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]

#[OA\Schema(schema: 'UserPublic', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'avatar_url', type: 'string', nullable: true),
    new OA\Property(property: 'role', type: 'string', enum: ['client', 'trainer']),
    new OA\Property(property: 'experience', type: 'string', nullable: true),
    new OA\Property(property: 'certifications', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'training_types', type: 'array', items: new OA\Items(type: 'string')),
])]

// ── Client Schemas ──────────────────────────────────────────────

#[OA\Schema(schema: 'Client', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'user_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'email', type: 'string', nullable: true),
    new OA\Property(property: 'phone', type: 'string', nullable: true),
    new OA\Property(property: 'avatar_url', type: 'string', nullable: true),
    new OA\Property(property: 'type', type: 'string', enum: ['personal', 'group', 'online']),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'notes', type: 'string', nullable: true),
    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'archived_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
])]

#[OA\Schema(schema: 'ClientInvitation', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'code', type: 'string'),
    new OA\Property(property: 'email', type: 'string'),
    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'accepted_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'revoked_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Exercise Schemas ────────────────────────────────────────────

#[OA\Schema(schema: 'Exercise', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'muscle_groups', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'equipment', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'video_file_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'archived_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Program Schemas ─────────────────────────────────────────────

#[OA\Schema(schema: 'Program', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'difficulty', type: 'string', enum: ['beginner', 'intermediate', 'advanced'], nullable: true),
    new OA\Property(property: 'estimated_duration_min', type: 'integer', nullable: true),
    new OA\Property(property: 'views_count', type: 'integer'),
    new OA\Property(property: 'likes_count', type: 'integer'),
    new OA\Property(property: 'archived_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'exercises', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProgramExercise')),
])]

#[OA\Schema(schema: 'ProgramExercise', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'order', type: 'integer'),
    new OA\Property(property: 'sets', type: 'integer'),
    new OA\Property(property: 'reps', type: 'integer'),
    new OA\Property(property: 'weight_kg', type: 'number', nullable: true),
    new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
    new OA\Property(property: 'notes', type: 'string', nullable: true),
    new OA\Property(property: 'name_snapshot', type: 'string'),
])]

#[OA\Schema(schema: 'ClientProgram', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'program_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'program_snapshot', type: 'object'),
    new OA\Property(property: 'assigned_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'removed_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Session Schemas ─────────────────────────────────────────────

#[OA\Schema(schema: 'Session', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'type', type: 'string', nullable: true),
    new OA\Property(property: 'start_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'end_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'status', type: 'string', enum: ['planned', 'in_progress', 'completed', 'canceled', 'no_show']),
    new OA\Property(property: 'status_changed_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'cancellation_reason', type: 'string', nullable: true),
    new OA\Property(property: 'notes', type: 'string', nullable: true),
    new OA\Property(property: 'program_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'client_package_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'series_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'google_event_id', type: 'string', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'participants', type: 'array', items: new OA\Items(ref: '#/components/schemas/SessionParticipant')),
])]

#[OA\Schema(schema: 'SessionParticipant', properties: [
    new OA\Property(property: 'session_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client', ref: '#/components/schemas/Client', nullable: true),
])]

// ── Chat Schemas ────────────────────────────────────────────────

#[OA\Schema(schema: 'Conversation', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'last_message_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'participants', type: 'array', items: new OA\Items(type: 'object', properties: [
        new OA\Property(property: 'user_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'last_read_at', type: 'string', format: 'date-time', nullable: true),
    ])),
    new OA\Property(property: 'last_message', ref: '#/components/schemas/Message', nullable: true),
    new OA\Property(property: 'unread_count', type: 'integer'),
])]

#[OA\Schema(schema: 'Message', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'conversation_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'sender_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'body', type: 'string', nullable: true),
    new OA\Property(property: 'media_file_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
    new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Workout Schemas ─────────────────────────────────────────────

#[OA\Schema(schema: 'WorkoutLog', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'session_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'started_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'started_by_user_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'finished_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'finished_by_user_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'last_version', type: 'integer', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'exercises', type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkoutLogExercise')),
])]

#[OA\Schema(schema: 'WorkoutLogExercise', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'order', type: 'integer'),
    new OA\Property(property: 'name_snapshot', type: 'string'),
    new OA\Property(property: 'planned_sets', type: 'integer', nullable: true),
    new OA\Property(property: 'planned_reps', type: 'integer', nullable: true),
    new OA\Property(property: 'planned_weight_kg', type: 'number', nullable: true),
    new OA\Property(property: 'sets', type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkoutLogSet')),
])]

#[OA\Schema(schema: 'WorkoutLogSet', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'workout_log_exercise_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'set_index', type: 'integer'),
    new OA\Property(property: 'reps', type: 'integer'),
    new OA\Property(property: 'weight_kg', type: 'number'),
    new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
    new OA\Property(property: 'performed_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'actor_user_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'is_pr', type: 'boolean'),
    new OA\Property(property: 'client_uuid', type: 'string', format: 'uuid'),
    new OA\Property(property: 'version', type: 'integer'),
])]

// ── Package Schemas ─────────────────────────────────────────────

#[OA\Schema(schema: 'PackageTemplate', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'kind', type: 'string', enum: ['count_based', 'time_based', 'hybrid']),
    new OA\Property(property: 'sessions_count', type: 'integer'),
    new OA\Property(property: 'validity_days', type: 'integer'),
    new OA\Property(property: 'price', type: 'number'),
    new OA\Property(property: 'currency', type: 'string'),
    new OA\Property(property: 'auto_renew_default', type: 'boolean'),
    new OA\Property(property: 'archived_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

#[OA\Schema(schema: 'ClientPackage', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'template_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'kind', type: 'string', enum: ['count_based', 'time_based', 'hybrid']),
    new OA\Property(property: 'sessions_count', type: 'integer'),
    new OA\Property(property: 'remaining_sessions', type: 'integer'),
    new OA\Property(property: 'validity_days', type: 'integer'),
    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'price', type: 'number'),
    new OA\Property(property: 'currency', type: 'string'),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'assigned_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'auto_renew', type: 'boolean'),
    new OA\Property(property: 'debt_since', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Transaction Schemas ─────────────────────────────────────────

#[OA\Schema(schema: 'Transaction', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'client_package_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'amount', type: 'number'),
    new OA\Property(property: 'currency', type: 'string'),
    new OA\Property(property: 'method', type: 'string', enum: ['cash', 'transfer', 'card', 'other']),
    new OA\Property(property: 'status', type: 'string', enum: ['paid', 'pending', 'canceled']),
    new OA\Property(property: 'paid_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'note', type: 'string', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

#[OA\Schema(schema: 'Withdrawal', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'trainer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'amount', type: 'number'),
    new OA\Property(property: 'currency', type: 'string'),
    new OA\Property(property: 'withdrawn_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'note', type: 'string', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Progress Schemas ────────────────────────────────────────────

#[OA\Schema(schema: 'BodyMeasurement', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'metric_type', type: 'string', enum: ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh']),
    new OA\Property(property: 'value', type: 'number'),
    new OA\Property(property: 'unit', type: 'string'),
    new OA\Property(property: 'measured_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'recorded_by_user_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

#[OA\Schema(schema: 'PersonalRecord', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'weight_kg', type: 'number'),
    new OA\Property(property: 'reps', type: 'integer'),
    new OA\Property(property: 'estimated_1rm', type: 'number'),
    new OA\Property(property: 'achieved_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
])]

// ── Notification Schemas ────────────────────────────────────────

#[OA\Schema(schema: 'Notification', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'type', type: 'string'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'body', type: 'string'),
    new OA\Property(property: 'payload', type: 'object'),
    new OA\Property(property: 'source_type', type: 'string'),
    new OA\Property(property: 'source_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]

// ── Common Schemas ──────────────────────────────────────────────

#[OA\Schema(schema: 'PaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
    new OA\Property(property: 'meta', properties: [
        new OA\Property(property: 'next_cursor', type: 'string', nullable: true),
        new OA\Property(property: 'has_more', type: 'boolean'),
    ], type: 'object'),
])]

#[OA\Schema(schema: 'ValidationError', properties: [
    new OA\Property(property: 'message', type: 'string'),
    new OA\Property(property: 'errors', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))),
])]

class Schemas
{
}
