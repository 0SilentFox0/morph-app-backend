<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageTemplateRequest;
use App\Http\Requests\UpdatePackageTemplateRequest;
use App\Http\Resources\PackageTemplateResource;
use App\Models\PackageTemplate;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PackageTemplateController extends Controller
{
    public function __construct(
        private readonly PackageService $packageService,
    ) {}

    #[OA\Get(
        path: '/package-templates',
        summary: 'List package templates',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of package templates',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/PackageTemplate')
                        ),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginatedResponse/properties/meta'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', PackageTemplate::class);

        $templates = $this->packageService->listTemplates(
            trainer: $request->user(),
        );

        return PackageTemplateResource::collection($templates);
    }

    #[OA\Post(
        path: '/package-templates',
        summary: 'Create a new package template',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'kind', 'sessions_count', 'validity_days', 'price', 'currency'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'kind', type: 'string', enum: ['count_based', 'time_based', 'hybrid']),
                    new OA\Property(property: 'sessions_count', type: 'integer', nullable: true),
                    new OA\Property(property: 'validity_days', type: 'integer', nullable: true),
                    new OA\Property(property: 'price', type: 'number'),
                    new OA\Property(property: 'currency', type: 'string'),
                    new OA\Property(property: 'auto_renew_default', type: 'boolean', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Package template created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/PackageTemplate'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StorePackageTemplateRequest $request): PackageTemplateResource
    {
        Gate::authorize('create', PackageTemplate::class);

        $template = $this->packageService->createTemplate(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return new PackageTemplateResource($template);
    }

    #[OA\Get(
        path: '/package-templates/{template}',
        summary: 'Get a package template',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'template', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Package template retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/PackageTemplate'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(PackageTemplate $template): PackageTemplateResource
    {
        Gate::authorize('view', $template);

        return new PackageTemplateResource($template);
    }

    #[OA\Put(
        path: '/package-templates/{template}',
        summary: 'Update a package template',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'template', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true),
                    new OA\Property(property: 'kind', type: 'string', enum: ['count_based', 'time_based', 'hybrid'], nullable: true),
                    new OA\Property(property: 'sessions_count', type: 'integer', nullable: true),
                    new OA\Property(property: 'validity_days', type: 'integer', nullable: true),
                    new OA\Property(property: 'price', type: 'number', nullable: true),
                    new OA\Property(property: 'currency', type: 'string', nullable: true),
                    new OA\Property(property: 'auto_renew_default', type: 'boolean', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Package template updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/PackageTemplate'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdatePackageTemplateRequest $request, PackageTemplate $template): PackageTemplateResource
    {
        Gate::authorize('update', $template);

        $template = $this->packageService->updateTemplate($template, $request->validated());

        return new PackageTemplateResource($template);
    }

    #[OA\Post(
        path: '/package-templates/{template}/archive',
        summary: 'Archive a package template',
        security: [['sanctum' => []]],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'template', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Package template archived',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/PackageTemplate'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function archive(PackageTemplate $template): JsonResponse
    {
        Gate::authorize('archive', $template);

        $this->packageService->archiveTemplate($template);

        return response()->json(['message' => 'Package template archived.'], 200);
    }
}
