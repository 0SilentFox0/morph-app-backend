<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeasurementRequest;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateOnboardingStepRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\BodyMeasurementResource;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserPublicResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WorkoutLogResource;
use App\Models\BodyMeasurement;
use App\Models\Client;
use App\Models\TrainingSession;
use App\Models\WorkoutLog;
use App\Services\ProgressService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ProgressService $progressService,
    ) {}

    /**
     * Get the authenticated user's own profile.
     */
    #[OA\Get(
        path: '/me',
        summary: 'Get the authenticated user\'s own profile',
        security: [['sanctum' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated user profile',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function me(Request $request): UserResource
    {
        $user = $this->userService->getProfile($request->user());

        return new UserResource($user);
    }

    /**
     * Update the authenticated user's profile.
     */
    #[OA\Put(
        path: '/me',
        summary: 'Update the authenticated user\'s profile',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
                    new OA\Property(property: 'experience', type: 'string', nullable: true),
                    new OA\Property(property: 'certifications', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'training_types', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'client_types', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'locations', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'work_schedule_start', type: 'string', example: '09:00', description: 'HH:mm format'),
                    new OA\Property(property: 'work_schedule_end', type: 'string', example: '18:00', description: 'HH:mm format'),
                    new OA\Property(property: 'work_schedule_days', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'goals', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'fitness_level', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated user profile',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateProfile(UpdateProfileRequest $request): UserResource
    {
        $user = $this->userService->updateProfile(
            user: $request->user(),
            data: $request->validated(),
        );

        return new UserResource($user);
    }

    /**
     * Update the authenticated user's settings.
     */
    #[OA\Put(
        path: '/me/settings',
        summary: 'Update the authenticated user\'s settings',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'timezone', type: 'string', example: 'Europe/London'),
                    new OA\Property(property: 'locale', type: 'string', example: 'en'),
                    new OA\Property(property: 'currency', type: 'string', example: 'GBP'),
                    new OA\Property(
                        property: 'notification_preferences',
                        type: 'object',
                        description: 'Key-value map of notification preference flags',
                    ),
                ],
            ),
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated user with new settings',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateSettings(UpdateSettingsRequest $request): UserResource
    {
        $user = $this->userService->updateSettings(
            user: $request->user(),
            data: $request->validated(),
        );

        return new UserResource($user);
    }

    /**
     * Update the authenticated user's avatar.
     */
    #[OA\Put(
        path: '/me/avatar',
        summary: 'Update the authenticated user\'s avatar',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['media_file_id'],
                properties: [
                    new OA\Property(property: 'media_file_id', type: 'string', format: 'uuid', example: '018f1e2a-3c4d-7b5e-9f0a-1b2c3d4e5f6a'),
                ],
            ),
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated user with new avatar',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateAvatar(UpdateAvatarRequest $request): UserResource
    {
        $user = $this->userService->updateAvatar(
            user: $request->user(),
            mediaFileId: $request->validated('media_file_id'),
        );

        return new UserResource($user);
    }

    /**
     * Get a user's public profile.
     */
    #[OA\Get(
        path: '/users/{id}',
        summary: 'Get a user\'s public profile',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the user',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public user profile',
                content: new OA\JsonContent(ref: '#/components/schemas/UserPublic'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'User not found'),
        ],
    )]
    public function show(string $id): UserPublicResource
    {
        $user = $this->userService->getPublicProfile($id);

        return new UserPublicResource($user);
    }

    /**
     * Mark onboarding as complete.
     */
    #[OA\Post(
        path: '/me/onboarding/complete',
        summary: 'Mark onboarding as complete',
        security: [['sanctum' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Onboarding marked complete',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function completeOnboarding(Request $request): UserResource
    {
        $user = $this->userService->completeOnboarding($request->user());

        return new UserResource($user);
    }

    /**
     * Get the authenticated user's onboarding progress.
     */
    #[OA\Get(
        path: '/me/onboarding',
        summary: 'Get the authenticated user\'s onboarding progress',
        security: [['sanctum' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Onboarding progress',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'steps', type: 'object', description: 'Map of step names to completion status'),
                                new OA\Property(property: 'current_step', type: 'string', nullable: true),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function onboarding(Request $request): JsonResponse
    {
        $progress = $this->userService->getOnboardingProgress($request->user());

        return response()->json([
            'data' => [
                'user_id'      => $progress->user_id,
                'steps'        => $progress->steps,
                'current_step' => $progress->current_step,
                'updated_at'   => $progress->updated_at,
            ],
        ]);
    }

    /**
     * Update a specific onboarding step.
     */
    #[OA\Put(
        path: '/me/onboarding/{step}',
        summary: 'Update a specific onboarding step',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['data'],
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        description: 'Arbitrary key-value data for the onboarding step',
                    ),
                ],
            ),
        ),
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'step',
                in: 'path',
                required: true,
                description: 'Onboarding step identifier',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated onboarding progress',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'steps', type: 'object', description: 'Map of step names to completion status'),
                                new OA\Property(property: 'current_step', type: 'string', nullable: true),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateOnboardingStep(UpdateOnboardingStepRequest $request, string $step): JsonResponse
    {
        $progress = $this->userService->updateOnboardingStep(
            user: $request->user(),
            step: $step,
            data: $request->validated('data'),
        );

        return response()->json([
            'data' => [
                'user_id'      => $progress->user_id,
                'steps'        => $progress->steps,
                'current_step' => $progress->current_step,
                'updated_at'   => $progress->updated_at,
            ],
        ]);
    }

    // ── Client-self endpoints (/me/*) ──────────────────────────────

    #[OA\Get(
        path: '/me/sessions',
        summary: 'Get the authenticated client\'s sessions',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of sessions for the authenticated client',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Session')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function mySessions(Request $request): AnonymousResourceCollection
    {
        $sessions = TrainingSession::whereHas('participants', function ($q) use ($request): void {
            $q->whereHas('client', function ($cq) use ($request): void {
                $cq->where('user_id', $request->user()->id);
            });
        })
            ->with('participants.client')
            ->orderBy('start_at', 'desc')
            ->paginate(perPage: $request->integer('per_page', 15));

        return SessionResource::collection($sessions);
    }

    #[OA\Get(
        path: '/me/measurements',
        summary: 'Get the authenticated client\'s body measurements',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'metric_type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh'])),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of body measurements',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BodyMeasurement')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function myMeasurements(Request $request): AnonymousResourceCollection
    {
        $clientId = $this->resolveClientId($request);

        if (!$clientId) {
            return BodyMeasurementResource::collection(collect());
        }

        $measurements = $this->progressService->listMeasurements(
            clientId: $clientId,
            filters: $request->only(['metric_type', 'from', 'to', 'per_page']),
        );

        return BodyMeasurementResource::collection($measurements);
    }

    #[OA\Post(
        path: '/me/measurements',
        summary: 'Store a body measurement for the authenticated client',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['metric_type', 'value', 'unit', 'measured_at'],
                properties: [
                    new OA\Property(property: 'metric_type', type: 'string', enum: ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh']),
                    new OA\Property(property: 'value', type: 'number', format: 'float', example: 75.5),
                    new OA\Property(property: 'unit', type: 'string', example: 'kg', maxLength: 8),
                    new OA\Property(property: 'measured_at', type: 'string', format: 'date-time', example: '2026-06-17T10:00:00Z'),
                ],
            ),
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Measurement created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/BodyMeasurement'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'No client record found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function storeMyMeasurement(StoreMeasurementRequest $request): JsonResponse
    {
        $clientId = $this->resolveClientId($request);

        if (!$clientId) {
            return response()->json(['message' => 'No client record found.'], 404);
        }

        $data = $request->validated();
        $data['client_id'] = $clientId;

        $measurement = $this->progressService->createMeasurement(
            data: $data,
            recordedBy: $request->user(),
        );

        return (new BodyMeasurementResource($measurement))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/me/workout-logs',
        summary: 'Get the authenticated client\'s workout logs',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of workout logs',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkoutLog')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function myWorkoutLogs(Request $request): AnonymousResourceCollection
    {
        $logs = WorkoutLog::whereHas('session.participants', function ($q) use ($request): void {
            $q->whereHas('client', function ($cq) use ($request): void {
                $cq->where('user_id', $request->user()->id);
            });
        })
            ->orderBy('started_at', 'desc')
            ->paginate(perPage: $request->integer('per_page', 15));

        return WorkoutLogResource::collection($logs);
    }

    private function resolveClientId(Request $request): ?string
    {
        return Client::where('user_id', $request->user()->id)->value('id');
    }
}
