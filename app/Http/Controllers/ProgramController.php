<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\AssignProgramRequest;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramExercisesRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Http\Resources\ClientProgramResource;
use App\Http\Resources\ProgramResource;
use App\Models\ClientProgram;
use App\Models\Program;
use App\Services\ProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ProgramController extends Controller
{
    public function __construct(
        private readonly ProgramService $programService,
    ) {}

    #[OA\Get(
        path: '/programs',
        summary: 'List programs',
        security: [['sanctum' => []]],
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'difficulty', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['beginner', 'intermediate', 'advanced'])),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'include_archived', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of programs',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Program')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Program::class);

        $programs = $this->programService->list(
            trainer: $request->user(),
            filters: $request->only(['q', 'difficulty', 'sort', 'per_page', 'include_archived']),
        );

        return ProgramResource::collection($programs);
    }

    #[OA\Post(
        path: '/programs',
        summary: 'Create a new program',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'difficulty'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Strength Foundations'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'difficulty', type: 'string', enum: ['beginner', 'intermediate', 'advanced']),
                    new OA\Property(property: 'estimated_duration_min', type: 'integer', nullable: true, example: 45),
                    new OA\Property(property: 'cover_file_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(
                        property: 'exercises',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'order', type: 'integer'),
                                new OA\Property(property: 'sets', type: 'integer', nullable: true),
                                new OA\Property(property: 'reps', type: 'integer', nullable: true),
                                new OA\Property(property: 'weight_kg', type: 'number', format: 'float', nullable: true),
                                new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
                                new OA\Property(property: 'notes', type: 'string', nullable: true),
                            ]
                        )
                    ),
                ]
            )
        ),
        tags: ['Programs'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Program created',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Program')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProgramRequest $request): JsonResponse
    {
        Gate::authorize('create', Program::class);

        $program = $this->programService->create(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return (new ProgramResource($program))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/programs/{program}',
        summary: 'Get a single program with exercises',
        security: [['sanctum' => []]],
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Program details with exercises',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Program')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Program $program): ProgramResource
    {
        Gate::authorize('view', $program);

        $program = $this->programService->show($program);

        return new ProgramResource($program);
    }

    #[OA\Put(
        path: '/programs/{program}',
        summary: 'Update a program',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Strength Foundations'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'difficulty', type: 'string', enum: ['beginner', 'intermediate', 'advanced']),
                    new OA\Property(property: 'estimated_duration_min', type: 'integer', nullable: true, example: 45),
                    new OA\Property(property: 'cover_file_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(
                        property: 'exercises',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'order', type: 'integer'),
                                new OA\Property(property: 'sets', type: 'integer', nullable: true),
                                new OA\Property(property: 'reps', type: 'integer', nullable: true),
                                new OA\Property(property: 'weight_kg', type: 'number', format: 'float', nullable: true),
                                new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
                                new OA\Property(property: 'notes', type: 'string', nullable: true),
                            ]
                        )
                    ),
                ]
            )
        ),
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Program updated',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Program')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateProgramRequest $request, Program $program): ProgramResource
    {
        Gate::authorize('update', $program);

        $program = $this->programService->update($program, $request->validated());

        return new ProgramResource($program);
    }

    #[OA\Post(
        path: '/programs/{program}/archive',
        summary: 'Archive a program',
        security: [['sanctum' => []]],
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Program archived',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Program')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function archive(Program $program): JsonResponse
    {
        Gate::authorize('archive', $program);

        $this->programService->archive($program);

        return response()->json(['message' => 'Program archived.'], 200);
    }

    #[OA\Post(
        path: '/programs/{program}/like',
        summary: 'Toggle like on a program',
        security: [['sanctum' => []]],
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Like toggled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'liked', type: 'boolean', example: true),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function like(Request $request, Program $program): JsonResponse
    {
        $this->programService->like($program, $request->user());

        return response()->json(['message' => 'Like toggled.'], 200);
    }

    #[OA\Put(
        path: '/programs/{program}/exercises',
        summary: 'Replace all exercises on a program',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['exercises'],
                properties: [
                    new OA\Property(
                        property: 'exercises',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'order', type: 'integer'),
                                new OA\Property(property: 'sets', type: 'integer', nullable: true),
                                new OA\Property(property: 'reps', type: 'integer', nullable: true),
                                new OA\Property(property: 'weight_kg', type: 'number', format: 'float', nullable: true),
                                new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
                                new OA\Property(property: 'notes', type: 'string', nullable: true),
                            ]
                        )
                    ),
                ]
            )
        ),
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Program exercises updated',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Program')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateExercises(UpdateProgramExercisesRequest $request, Program $program): ProgramResource
    {
        Gate::authorize('update', $program);

        $this->programService->updateExercises($program, $request->validated()['exercises']);

        return new ProgramResource($program->refresh()->load('exercises.exercise'));
    }

    #[OA\Post(
        path: '/programs/{program}/assign',
        summary: 'Assign a program to a client',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['client_id'],
                properties: [
                    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
                ]
            )
        ),
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Program assigned to client',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ClientProgram')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function assign(AssignProgramRequest $request, Program $program): JsonResponse
    {
        Gate::authorize('update', $program);

        $client = Client::findOrFail($request->validated()['client_id']);

        $clientProgram = $this->programService->assignToClient($program, $client);

        return (new ClientProgramResource($clientProgram))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Delete(
        path: '/client-programs/{clientProgram}',
        summary: 'Remove a program assignment from a client',
        security: [['sanctum' => []]],
        tags: ['Programs'],
        parameters: [
            new OA\Parameter(name: 'clientProgram', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Assignment removed'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function removeAssignment(ClientProgram $clientProgram): JsonResponse
    {
        $this->programService->removeFromClient($clientProgram);

        return response()->json(['message' => 'Assignment removed.'], 200);
    }
}
