<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Http\Requests\AddExerciseRequest;
use App\Http\Requests\LogSetRequest;
use App\Http\Requests\StartWorkoutRequest;
use App\Http\Requests\UpdateSetRequest;
use App\Http\Resources\WorkoutLogExerciseResource;
use App\Http\Resources\WorkoutLogResource;
use App\Http\Resources\WorkoutLogSetResource;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use App\Models\WorkoutLogSet;
use App\Services\WorkoutTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class WorkoutLogController extends Controller
{
    public function __construct(
        private readonly WorkoutTrackingService $workoutTrackingService,
    ) {}

    #[OA\Post(
        path: '/sessions/{session}/workout',
        summary: 'Start a workout log for a session',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'session', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Workout log created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/WorkoutLog'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function start(StartWorkoutRequest $request, TrainingSession $session): JsonResponse
    {
        $log = $this->workoutTrackingService->startWorkout($session, $request->user());

        return (new WorkoutLogResource($log))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: '/workout-logs/{log}/finish',
        summary: 'Finish an active workout log',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'log', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Workout log finished',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/WorkoutLog'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function finish(Request $request, WorkoutLog $log): WorkoutLogResource
    {
        Gate::authorize('update', $log);

        $log = $this->workoutTrackingService->finishWorkout($log, $request->user());

        return new WorkoutLogResource($log);
    }

    #[OA\Get(
        path: '/workout-logs/{log}',
        summary: 'Get a workout log',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'log', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Workout log retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/WorkoutLog'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(WorkoutLog $log): WorkoutLogResource
    {
        Gate::authorize('view', $log);

        return new WorkoutLogResource(
            $this->workoutTrackingService->getLog($log),
        );
    }

    #[OA\Post(
        path: '/workout-logs/{log}/exercises',
        summary: 'Add an exercise to a workout log',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'log', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['exercise_id', 'name_snapshot'],
                properties: [
                    new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name_snapshot', type: 'string'),
                    new OA\Property(property: 'planned_sets', type: 'integer', nullable: true),
                    new OA\Property(property: 'planned_reps', type: 'integer', nullable: true),
                    new OA\Property(property: 'planned_weight_kg', type: 'number', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Exercise added to workout log',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/WorkoutLogExercise'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function addExercise(AddExerciseRequest $request, WorkoutLog $log): JsonResponse
    {
        Gate::authorize('update', $log);

        $exercise = $this->workoutTrackingService->addExercise($log, $request->validated());

        return (new WorkoutLogExerciseResource($exercise))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: '/workout-logs/{log}/sets',
        summary: 'Log a set to a workout log',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'log', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['workout_log_exercise_id', 'exercise_id', 'set_index', 'reps', 'weight_kg', 'client_uuid'],
                properties: [
                    new OA\Property(property: 'workout_log_exercise_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'exercise_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'set_index', type: 'integer'),
                    new OA\Property(property: 'reps', type: 'integer'),
                    new OA\Property(property: 'weight_kg', type: 'number'),
                    new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
                    new OA\Property(property: 'client_uuid', type: 'string', format: 'uuid'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Set logged',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/WorkoutLogSet'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function logSet(LogSetRequest $request, WorkoutLog $log): JsonResponse
    {
        Gate::authorize('logSet', $log);

        $validated = $request->validated();
        $exercise = WorkoutLogExercise::findOrFail($validated['workout_log_exercise_id']);

        $set = $this->workoutTrackingService->logSet(
            $log,
            $exercise,
            $validated,
            $request->user(),
        );

        return (new WorkoutLogSetResource($set))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/workout-log-sets/{set}',
        summary: 'Update a logged set',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'set', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reps', type: 'integer', nullable: true),
                    new OA\Property(property: 'weight_kg', type: 'number', nullable: true),
                    new OA\Property(property: 'rest_seconds', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Set updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/WorkoutLogSet'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function updateSet(UpdateSetRequest $request, WorkoutLogSet $set): WorkoutLogSetResource
    {
        $log = $set->workoutLog;
        Gate::authorize('update', $log);

        $set = $this->workoutTrackingService->updateSet($set, $request->validated(), $request->user());

        return new WorkoutLogSetResource($set);
    }

    #[OA\Delete(
        path: '/workout-log-sets/{set}',
        summary: 'Delete a logged set',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'set', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Set deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function deleteSet(WorkoutLogSet $set): JsonResponse
    {
        $log = $set->workoutLog;
        Gate::authorize('update', $log);

        $this->workoutTrackingService->deleteSet($set);

        return response()->json(null, 204);
    }

    #[OA\Get(
        path: '/workout-logs',
        summary: 'List workout log history',
        security: [['sanctum' => []]],
        tags: ['Workouts'],
        parameters: [
            new OA\Parameter(name: 'session_id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of workout logs',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/WorkoutLog')
                        ),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedResponse/properties/meta'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function history(Request $request): AnonymousResourceCollection
    {
        $logs = $this->workoutTrackingService->getHistory(
            $request->user(),
            $request->only(['session_id', 'per_page']),
        );

        return WorkoutLogResource::collection($logs);
    }
}
