<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonalRecordResource;
use App\Models\BodyMeasurement;
use App\Services\ProgressService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PersonalRecordController extends Controller
{
    public function __construct(
        private readonly ProgressService $progressService,
    ) {}

    #[OA\Get(
        path: '/clients/{client}/personal-records',
        summary: 'List personal records for a client',
        security: [['sanctum' => []]],
        tags: ['Progress'],
        parameters: [
            new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of personal records',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonalRecord')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function index(string $client): AnonymousResourceCollection
    {
        Gate::authorize('view', [BodyMeasurement::class, $client]);

        $records = $this->progressService->listPersonalRecords(
            clientId: $client,
        );

        return PersonalRecordResource::collection($records);
    }
}
