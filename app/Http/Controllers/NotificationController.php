<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterDeviceTokenRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    #[OA\Get(
        path: '/notifications',
        summary: 'List notifications for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cursor-paginated list of notifications',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Notification')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        $notifications = Notification::where('recipient_user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->cursorPaginate(perPage: $request->integer('per_page', 20));

        return NotificationResource::collection($notifications);
    }

    #[OA\Post(
        path: '/notifications/{id}/read',
        summary: 'Mark a single notification as read',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marked as read',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = Notification::where('recipient_user_id', $request->user()->id)
            ->findOrFail($id);

        $this->notificationService->markAsRead($notification);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    #[OA\Post(
        path: '/notifications/read-all',
        summary: 'Mark all notifications as read',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All notifications marked as read',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    #[OA\Get(
        path: '/notifications/unread-count',
        summary: 'Get the count of unread notifications',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread notification count',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'unread_count', type: 'integer', example: 5),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return response()->json(['data' => ['unread_count' => $count]]);
    }

    #[OA\Post(
        path: '/device-tokens',
        summary: 'Register a device token for push notifications',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'platform'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'fcm-device-token-abc123'),
                    new OA\Property(property: 'platform', type: 'string', enum: ['ios', 'android']),
                    new OA\Property(property: 'device_label', type: 'string', nullable: true, example: 'iPhone 15'),
                    new OA\Property(property: 'app_version', type: 'string', nullable: true, example: '1.2.0'),
                ]
            )
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Device token registered',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'token', type: 'string'),
                                new OA\Property(property: 'platform', type: 'string'),
                                new OA\Property(property: 'device_label', type: 'string', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function registerDeviceToken(RegisterDeviceTokenRequest $request): JsonResponse
    {
        $deviceToken = $this->notificationService->registerDeviceToken(
            user: $request->user(),
            token: $request->validated('token'),
            platform: $request->validated('platform'),
            deviceLabel: $request->validated('device_label'),
            appVersion: $request->validated('app_version'),
        );

        return response()->json([
            'data' => [
                'id'           => $deviceToken->id,
                'token'        => $deviceToken->token,
                'platform'     => $deviceToken->platform,
                'device_label' => $deviceToken->device_label,
                'created_at'   => $deviceToken->created_at,
            ],
        ], 201);
    }

    #[OA\Delete(
        path: '/device-tokens/{token}',
        summary: 'Remove a device token',
        security: [['sanctum' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'token', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Device token removed'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function removeDeviceToken(string $token): JsonResponse
    {
        $this->notificationService->removeDeviceToken($token);

        return response()->json(['message' => 'Device token removed.']);
    }
}
