<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use App\Services\ExerciseService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ExerciseController extends Controller
{
    public function __construct(
        private readonly ExerciseService $exerciseService,
    ) {}

    #[OA\Get(
        path: '/exercises',
        summary: 'List exercises',
        security: [['sanctum' => []]],
        tags: ['Exercises'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'muscle_group', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'include_archived', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of exercises',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Exercise')),
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
        Gate::authorize('viewAny', Exercise::class);

        $exercises = $this->exerciseService->list(
            trainer: $request->user(),
            filters: $request->only(['q', 'muscle_group', 'sort', 'per_page', 'include_archived']),
        );

        return ExerciseResource::collection($exercises);
    }

    #[OA\Post(
        path: '/exercises',
        summary: 'Create a new exercise',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Bench Press'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'muscle_groups', type: 'array', items: new OA\Items(type: 'string'), example: ['chest', 'triceps']),
                    new OA\Property(property: 'equipment', type: 'array', items: new OA\Items(type: 'string'), example: ['barbell', 'bench']),
                    new OA\Property(property: 'video_file_id', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        tags: ['Exercises'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Exercise created',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Exercise')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreExerciseRequest $request): ExerciseResource
    {
        Gate::authorize('create', Exercise::class);

        $exercise = $this->exerciseService->create(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return new ExerciseResource($exercise);
    }

    #[OA\Get(
        path: '/exercises/{exercise}',
        summary: 'Get a single exercise',
        security: [['sanctum' => []]],
        tags: ['Exercises'],
        parameters: [
            new OA\Parameter(name: 'exercise', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Exercise details',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Exercise')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Exercise $exercise): ExerciseResource
    {
        Gate::authorize('view', $exercise);

        return new ExerciseResource($exercise);
    }

    #[OA\Put(
        path: '/exercises/{exercise}',
        summary: 'Update an exercise',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Bench Press'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'muscle_groups', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'equipment', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'video_file_id', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        tags: ['Exercises'],
        parameters: [
            new OA\Parameter(name: 'exercise', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Exercise updated',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Exercise')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateExerciseRequest $request, Exercise $exercise): ExerciseResource
    {
        Gate::authorize('update', $exercise);

        $exercise = $this->exerciseService->update($exercise, $request->validated());

        return new ExerciseResource($exercise);
    }

    #[OA\Post(
        path: '/exercises/{exercise}/archive',
        summary: 'Archive an exercise',
        security: [['sanctum' => []]],
        tags: ['Exercises'],
        parameters: [
            new OA\Parameter(name: 'exercise', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Exercise archived',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Exercise')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function archive(Exercise $exercise): ExerciseResource
    {
        Gate::authorize('archive', $exercise);

        $exercise = $this->exerciseService->archive($exercise);

        return new ExerciseResource($exercise);
    }

    #[OA\Post(
        path: '/exercises/{exercise}/restore',
        summary: 'Restore an archived exercise',
        security: [['sanctum' => []]],
        tags: ['Exercises'],
        parameters: [
            new OA\Parameter(name: 'exercise', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Exercise restored',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Exercise')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function restore(Exercise $exercise): ExerciseResource
    {
        Gate::authorize('archive', $exercise);

        $exercise = $this->exerciseService->restore($exercise);

        return new ExerciseResource($exercise);
    }
}
