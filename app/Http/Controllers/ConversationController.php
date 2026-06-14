<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    #[OA\Get(
        path: '/conversations',
        summary: 'List all conversations for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Chat'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of conversations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Conversation')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $this->chatService->getConversations($request->user());

        return ConversationResource::collection($conversations);
    }

    #[OA\Post(
        path: '/conversations',
        summary: 'Get or create a conversation with another user',
        security: [['sanctum' => []]],
        tags: ['Chat'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Conversation created or returned',
                content: new OA\JsonContent(ref: '#/components/schemas/Conversation')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CreateConversationRequest $request): JsonResponse
    {
        $conversation = $this->chatService->getOrCreateConversation(
            $request->user(),
            $request->validated('user_id'),
        );

        return (new ConversationResource($conversation))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/conversations/{conversation}/messages',
        summary: 'Get messages for a conversation',
        security: [['sanctum' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'conversation', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of messages',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Message')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function messages(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        $this->authorize('view', $conversation);

        $messages = $this->chatService->getMessages(
            $conversation,
            $request->query('cursor'),
        );

        return MessageResource::collection($messages);
    }
}
