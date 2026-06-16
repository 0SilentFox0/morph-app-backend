<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    #[OA\Get(
        path: '/transactions',
        summary: 'List transactions',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'client_id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['paid', 'pending', 'canceled'])),
            new OA\Parameter(name: 'method', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['cash', 'transfer', 'card', 'other'])),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of transactions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Transaction')),
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
        Gate::authorize('viewAny', Transaction::class);

        $transactions = $this->transactionService->list(
            trainer: $request->user(),
            filters: $request->only(['client_id', 'status', 'method', 'from', 'to', 'sort', 'per_page']),
        );

        return TransactionResource::collection($transactions);
    }

    #[OA\Post(
        path: '/transactions',
        summary: 'Create a new transaction',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'currency', 'method', 'status'],
                properties: [
                    new OA\Property(property: 'client_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'client_package_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 150.00),
                    new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                    new OA\Property(property: 'method', type: 'string', enum: ['cash', 'transfer', 'card', 'other']),
                    new OA\Property(property: 'status', type: 'string', enum: ['paid', 'pending', 'canceled']),
                    new OA\Property(property: 'paid_at', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                    new OA\Property(property: 'idempotency_key', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Transaction created',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Transaction')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        Gate::authorize('create', Transaction::class);

        $transaction = $this->transactionService->create(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/transactions/{transaction}',
        summary: 'Get a single transaction',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'transaction', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction details',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Transaction')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Transaction $transaction): TransactionResource
    {
        Gate::authorize('view', $transaction);

        $transaction = $this->transactionService->show($transaction);

        return new TransactionResource($transaction);
    }

    #[OA\Put(
        path: '/transactions/{transaction}',
        summary: 'Update a transaction',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'client_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'client_package_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'currency', type: 'string', nullable: true),
                    new OA\Property(property: 'method', type: 'string', enum: ['cash', 'transfer', 'card', 'other'], nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['paid', 'pending', 'canceled'], nullable: true),
                    new OA\Property(property: 'paid_at', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                    new OA\Property(property: 'idempotency_key', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'transaction', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction updated',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Transaction')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        Gate::authorize('update', $transaction);

        $transaction = $this->transactionService->update($transaction, $request->validated());

        return new TransactionResource($transaction);
    }

    #[OA\Delete(
        path: '/transactions/{transaction}',
        summary: 'Delete a transaction',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'transaction', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Transaction deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Transaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);

        $this->transactionService->delete($transaction);

        return response()->json(['message' => 'Transaction deleted.'], 200);
    }
}
