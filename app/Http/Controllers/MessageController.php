<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarkAsReadRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

class MessageController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    #[OA\Post(
        path: '/conversations/{conversation}/messages',
        summary: 'Send a message in a conversation',
        security: [['sanctum' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'conversation', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['body'],
                properties: [
                    new OA\Property(property: 'body', type: 'string'),
                    new OA\Property(property: 'media_file_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'client_message_id', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Message sent',
                content: new OA\JsonContent(ref: '#/components/schemas/Message')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('sendMessage', $conversation);

        $message = $this->chatService->sendMessage(
            $request->user(),
            $conversation,
            $request->validated(),
        );

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: '/conversations/{conversation}/read',
        summary: 'Mark messages in a conversation as read',
        security: [['sanctum' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'conversation', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['message_id'],
                properties: [
                    new OA\Property(property: 'message_id', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Messages marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function markAsRead(MarkAsReadRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $this->chatService->markAsRead(
            $request->user(),
            $conversation,
            $request->validated('message_id'),
        );

        return response()->json(['status' => 'ok']);
    }

    #[OA\Delete(
        path: '/messages/{message}',
        summary: 'Delete a message',
        security: [['sanctum' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'message', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Message deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Message $message): JsonResponse
    {
        $this->authorize('delete', $message);

        $this->chatService->deleteMessage($message);

        return response()->json(null, 204);
    }
}
