<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateOnboardingStepRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\UserPublicResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
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
}
