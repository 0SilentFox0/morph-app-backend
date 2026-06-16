<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionRequest;
use App\Http\Requests\StoreSessionSeriesRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Http\Requests\UpdateSessionStatusRequest;
use App\Http\Resources\SessionResource;
use App\Models\TrainingSession;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class SessionController extends Controller
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    #[OA\Get(
        path: '/sessions/schedule',
        summary: 'Get sessions schedule for a date range',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of sessions in the given date range',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Session')
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function schedule(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $sessions = $this->sessionService->getSchedule(
            $request->user(),
            $request->input('from'),
            $request->input('to'),
        );

        return SessionResource::collection($sessions);
    }

    #[OA\Get(
        path: '/sessions',
        summary: 'List sessions with optional filters',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['planned', 'in_progress', 'completed', 'canceled', 'no_show'])),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'client_id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of sessions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Session')),
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
        $sessions = $this->sessionService->list(
            $request->user(),
            $request->only(['status', 'from', 'to', 'client_id', 'per_page']),
        );

        return SessionResource::collection($sessions);
    }

    #[OA\Post(
        path: '/sessions',
        summary: 'Create a new training session',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'type', 'start_at', 'end_at'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'start_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'end_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(property: 'program_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'client_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'idempotency_key', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Session created',
                content: new OA\JsonContent(ref: '#/components/schemas/Session')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreSessionRequest $request): JsonResponse
    {
        $session = $this->sessionService->create(
            $request->user(),
            $request->validated(),
        );

        return (new SessionResource($session))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/sessions/{session}',
        summary: 'Get a single training session',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'session', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Session details',
                content: new OA\JsonContent(ref: '#/components/schemas/Session')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(TrainingSession $session): SessionResource
    {
        Gate::authorize('view', $session);

        $session->load('participants');

        return new SessionResource(
            $this->sessionService->show($session),
        );
    }

    #[OA\Put(
        path: '/sessions/{session}',
        summary: 'Update a training session',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'session', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'start_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'end_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(property: 'program_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'client_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Session updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Session')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateSessionRequest $request, TrainingSession $session): SessionResource
    {
        Gate::authorize('update', $session);

        $session = $this->sessionService->update($session, $request->validated());

        return new SessionResource($session);
    }

    #[OA\Post(
        path: '/sessions/{session}/status',
        summary: 'Update the status of a training session',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'session', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['planned', 'in_progress', 'completed', 'canceled', 'no_show']),
                    new OA\Property(property: 'cancellation_reason', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Session status updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Session')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateStatus(UpdateSessionStatusRequest $request, TrainingSession $session): SessionResource
    {
        Gate::authorize('update', $session);

        $session = $this->sessionService->updateStatus(
            $session,
            $request->validated('status'),
            $request->validated('cancellation_reason'),
        );

        return new SessionResource($session);
    }

    #[OA\Post(
        path: '/sessions/{session}/cancel',
        summary: 'Cancel a training session',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'session', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Session cancelled',
                content: new OA\JsonContent(ref: '#/components/schemas/Session')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function cancel(Request $request, TrainingSession $session): SessionResource
    {
        Gate::authorize('cancel', $session);

        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $session = $this->sessionService->cancel($session, $request->input('reason'));

        return new SessionResource($session);
    }

    #[OA\Delete(
        path: '/sessions/{session}',
        summary: 'Delete a training session',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\Parameter(name: 'session', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Session deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(TrainingSession $session): JsonResponse
    {
        Gate::authorize('delete', $session);

        $this->sessionService->delete($session);

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: '/session-series',
        summary: 'Create a recurring series of training sessions',
        security: [['sanctum' => []]],
        tags: ['Sessions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'type', 'duration_minutes', 'recurrence_rule', 'timezone'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'duration_minutes', type: 'integer'),
                    new OA\Property(property: 'client_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'program_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(
                        property: 'recurrence_rule',
                        type: 'object',
                        required: ['frequency', 'time', 'until_date'],
                        properties: [
                            new OA\Property(property: 'frequency', type: 'string'),
                            new OA\Property(property: 'days_of_week', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'time', type: 'string', description: 'HH:MM time of day'),
                            new OA\Property(property: 'until_date', type: 'string', format: 'date'),
                        ]
                    ),
                    new OA\Property(property: 'timezone', type: 'string', description: 'IANA timezone identifier'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Session series created',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Session')
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function storeSeries(StoreSessionSeriesRequest $request): JsonResponse
    {
        $series = $this->sessionService->createSeries(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'data' => [
                'id'                => $series->id,
                'trainer_id'        => $series->trainer_id,
                'template'          => $series->template,
                'recurrence_rule'   => $series->recurrence_rule,
                'timezone'          => $series->timezone,
                'materialized_until' => $series->materialized_until,
                'sessions'          => SessionResource::collection($series->sessions),
            ],
        ], 201);
    }
}
