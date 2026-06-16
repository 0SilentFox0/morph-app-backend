<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientInvitationResource;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\ClientInvitation;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    #[OA\Get(
        path: '/clients',
        summary: 'List all clients for the authenticated trainer',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['personal', 'group', 'online'])),
            new OA\Parameter(name: 'q', in: 'query', required: false, description: 'Search query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of clients',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Client'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Client::class);

        $clients = $this->clientService->list(
            trainer: $request->user(),
            filters: $request->only(['status', 'type', 'q', 'sort', 'per_page']),
        );

        return ClientResource::collection($clients);
    }

    #[OA\Post(
        path: '/clients',
        summary: 'Create a new client',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'type'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Smith'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                    new OA\Property(property: 'phone', type: 'string', nullable: true),
                    new OA\Property(property: 'type', type: 'string', enum: ['personal', 'group', 'online']),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
                ],
            ),
        ),
        tags: ['Clients'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Client created',
                content: new OA\JsonContent(ref: '#/components/schemas/Client'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreClientRequest $request): JsonResponse
    {
        Gate::authorize('create', Client::class);

        $client = $this->clientService->create(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/clients/{client}',
        summary: 'Get a single client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'UUID of the client',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client resource',
                content: new OA\JsonContent(ref: '#/components/schemas/Client'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Client not found'),
        ],
    )]
    public function show(Client $client): ClientResource
    {
        Gate::authorize('view', $client);

        $client = $this->clientService->show($client);

        return new ClientResource($client);
    }

    #[OA\Put(
        path: '/clients/{client}',
        summary: 'Update a client',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                    new OA\Property(property: 'phone', type: 'string', nullable: true),
                    new OA\Property(property: 'type', type: 'string', enum: ['personal', 'group', 'online']),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
                ],
            ),
        ),
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'UUID of the client',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated client',
                content: new OA\JsonContent(ref: '#/components/schemas/Client'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Client not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        Gate::authorize('update', $client);

        $client = $this->clientService->update($client, $request->validated());

        return new ClientResource($client);
    }

    #[OA\Post(
        path: '/clients/{client}/archive',
        summary: 'Archive a client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'UUID of the client',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Archived client',
                content: new OA\JsonContent(ref: '#/components/schemas/Client'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Client not found'),
        ],
    )]
    public function archive(Client $client): ClientResource
    {
        Gate::authorize('archive', $client);

        $client = $this->clientService->archive($client);

        return new ClientResource($client);
    }

    #[OA\Post(
        path: '/clients/{client}/restore',
        summary: 'Restore an archived client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'UUID of the client',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Restored client',
                content: new OA\JsonContent(ref: '#/components/schemas/Client'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Client not found'),
        ],
    )]
    public function restore(Client $client): ClientResource
    {
        Gate::authorize('restore', $client);

        $client = $this->clientService->restore($client);

        return new ClientResource($client);
    }

    #[OA\Post(
        path: '/clients/{client}/invite',
        summary: 'Send an invitation to a client',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'UUID of the client',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Client invitation created',
                content: new OA\JsonContent(ref: '#/components/schemas/ClientInvitation'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Client not found'),
        ],
    )]
    public function invite(Client $client): JsonResponse
    {
        Gate::authorize('invite', $client);

        $invitation = $this->clientService->invite($client);

        return (new ClientInvitationResource($invitation))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: '/client-invitations/{code}/accept',
        summary: 'Accept a client invitation by code',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                description: 'Invitation code',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Invitation accepted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Invitation accepted.'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Invitation not found or expired'),
        ],
    )]
    public function acceptInvitation(Request $request, string $code): ClientResource
    {
        $client = $this->clientService->acceptInvitation($code, $request->user());

        return new ClientResource($client);
    }

    #[OA\Delete(
        path: '/client-invitations/{invitation}',
        summary: 'Revoke a client invitation',
        security: [['sanctum' => []]],
        tags: ['Clients'],
        parameters: [
            new OA\Parameter(
                name: 'invitation',
                in: 'path',
                required: true,
                description: 'UUID of the invitation',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Invitation revoked'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Invitation not found'),
        ],
    )]
    public function revokeInvitation(ClientInvitation $invitation): JsonResponse
    {
        $this->clientService->revokeInvitation($invitation);

        return response()->json(['message' => 'Invitation revoked.'], 200);
    }
}
