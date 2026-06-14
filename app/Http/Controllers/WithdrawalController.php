<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawalRequest;
use App\Http\Resources\WithdrawalResource;
use App\Models\Withdrawal;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class WithdrawalController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    #[OA\Get(
        path: '/withdrawals',
        summary: 'List withdrawals',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of withdrawals',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Withdrawal')),
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
        Gate::authorize('viewAny', Withdrawal::class);

        $withdrawals = $this->transactionService->listWithdrawals(
            trainer: $request->user(),
        );

        return WithdrawalResource::collection($withdrawals);
    }

    #[OA\Post(
        path: '/withdrawals',
        summary: 'Create a new withdrawal',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'currency', 'withdrawn_at'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 500.00),
                    new OA\Property(property: 'currency', type: 'string', example: 'USD'),
                    new OA\Property(property: 'withdrawn_at', type: 'string', format: 'date'),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Withdrawal created',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Withdrawal')]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreWithdrawalRequest $request): WithdrawalResource
    {
        Gate::authorize('create', Withdrawal::class);

        $withdrawal = $this->transactionService->createWithdrawal(
            trainer: $request->user(),
            data: $request->validated(),
        );

        return new WithdrawalResource($withdrawal);
    }

    #[OA\Delete(
        path: '/withdrawals/{withdrawal}',
        summary: 'Delete a withdrawal',
        security: [['sanctum' => []]],
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'withdrawal', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Withdrawal deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Withdrawal $withdrawal): JsonResponse
    {
        Gate::authorize('delete', $withdrawal);

        $this->transactionService->deleteWithdrawal($withdrawal);

        return response()->json(['message' => 'Withdrawal deleted.'], 200);
    }
}
