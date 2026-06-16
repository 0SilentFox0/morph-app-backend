<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeasurementHistoryRequest;
use App\Http\Requests\StoreMeasurementRequest;
use App\Http\Resources\BodyMeasurementResource;
use App\Models\BodyMeasurement;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class BodyMeasurementController extends Controller
{
    public function __construct(
        private readonly ProgressService $progressService,
    ) {}

    #[OA\Get(
        path: '/clients/{client}/measurements',
        summary: 'List body measurements for a client',
        security: [['sanctum' => []]],
        tags: ['Progress'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'metric_type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh'])),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of body measurements',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BodyMeasurement')),
                        new OA\Property(property: 'meta', type: 'object'),
                        new OA\Property(property: 'links', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function index(Request $request, string $client): AnonymousResourceCollection
    {
        Gate::authorize('view', [BodyMeasurement::class, $client]);

        $measurements = $this->progressService->listMeasurements(
            clientId: $client,
            filters: $request->only(['metric_type', 'from', 'to', 'per_page']),
        );

        return BodyMeasurementResource::collection($measurements);
    }

    #[OA\Post(
        path: '/clients/{client}/measurements',
        summary: 'Record a new body measurement for a client',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['metric_type', 'value', 'unit', 'measured_at'],
                properties: [
                    new OA\Property(property: 'metric_type', type: 'string', enum: ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh']),
                    new OA\Property(property: 'value', type: 'number', format: 'float', example: 75.5),
                    new OA\Property(property: 'unit', type: 'string', example: 'kg'),
                    new OA\Property(property: 'measured_at', type: 'string', format: 'date'),
                ]
            )
        ),
        tags: ['Progress'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Body measurement recorded',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/BodyMeasurement')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreMeasurementRequest $request, string $client): JsonResponse
    {
        Gate::authorize('create', [BodyMeasurement::class, $client]);

        $data = $request->validated();
        $data['client_id'] = $client;

        $measurement = $this->progressService->createMeasurement(
            data: $data,
            recordedBy: $request->user(),
        );

        return (new BodyMeasurementResource($measurement))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Delete(
        path: '/measurements/{measurement}',
        summary: 'Delete a body measurement',
        security: [['sanctum' => []]],
        tags: ['Progress'],
        parameters: [
            new OA\Parameter(name: 'measurement', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Measurement deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(BodyMeasurement $measurement): JsonResponse
    {
        Gate::authorize('delete', $measurement);

        $this->progressService->deleteMeasurement($measurement);

        return response()->json(['message' => 'Measurement deleted.'], 200);
    }

    #[OA\Get(
        path: '/clients/{client}/measurements/history',
        summary: 'Get measurement history for a client by metric type',
        security: [['sanctum' => []]],
        tags: ['Progress'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'metric_type', in: 'query', required: true, schema: new OA\Schema(type: 'string', enum: ['weight', 'height', 'body_fat_percent', 'chest', 'waist', 'hips', 'biceps', 'thigh'])),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Measurement history',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BodyMeasurement')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function history(MeasurementHistoryRequest $request, string $client): AnonymousResourceCollection
    {
        Gate::authorize('view', [BodyMeasurement::class, $client]);

        $measurements = $this->progressService->getMeasurementHistory(
            clientId: $client,
            metricType: $request->validated('metric_type'),
        );

        return BodyMeasurementResource::collection($measurements);
    }
}
