<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignClientPackageRequest;
use App\Http\Resources\ClientPackageResource;
use App\Models\ClientPackage;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ClientPackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packageService,
    ) {}

    #[OA\Get(
        path: '/client-packages',
        summary: 'List client packages',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'client_id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'has_debt', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of client packages',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ClientPackage')
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ClientPackage::class);

        $packages = $this->packageService->listClientPackages(
            trainer: $request->user(),
            filters: $request->only(['client_id', 'status', 'has_debt', 'per_page']),
        );

        return ClientPackageResource::collection($packages);
    }

    #[OA\Post(
        path: '/client-packages',
        summary: 'Assign a package to a client',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['client_id', 'kind', 'sessions_count', 'validity_days', 'price', 'currency'],
                properties: [
                    new OA\Property(property: 'client_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'template_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'kind', type: 'string', enum: ['count_based', 'time_based', 'hybrid']),
                    new OA\Property(property: 'sessions_count', type: 'integer', nullable: true),
                    new OA\Property(property: 'validity_days', type: 'integer', nullable: true),
                    new OA\Property(property: 'price', type: 'number'),
                    new OA\Property(property: 'currency', type: 'string'),
                    new OA\Property(property: 'auto_renew', type: 'boolean', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Client package assigned',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ClientPackage'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(AssignClientPackageRequest $request): JsonResponse
    {
        Gate::authorize('create', ClientPackage::class);

        $package = $this->packageService->assignPackage(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return (new ClientPackageResource($package))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/client-packages/{package}',
        summary: 'Get a client package',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'package', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client package retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ClientPackage'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(ClientPackage $package): ClientPackageResource
    {
        Gate::authorize('view', $package);

        $package = $this->packageService->showPackage($package);

        return new ClientPackageResource($package);
    }

    #[OA\Post(
        path: '/client-packages/{package}/archive',
        summary: 'Archive a client package',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'package', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client package archived',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ClientPackage'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function archive(ClientPackage $package): JsonResponse
    {
        Gate::authorize('archive', $package);

        $this->packageService->archivePackage($package);

        return response()->json(['message' => 'Client package archived.'], 200);
    }
}
