<?php

namespace App\Services;

use App\Models\User;
use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Contracts\Pagination\CursorPaginator;

class TransactionService
{
    public function list(User $trainer, array $filters = []): CursorPaginator
    {
        $query = Transaction::where('trainer_id', $trainer->id);

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $allowedSorts = ['created_at', 'amount', 'paid_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function create(User $trainer, array $data): Transaction
    {
        $data['trainer_id'] = $trainer->id;

        $transaction = Transaction::create($data);

        TransactionCreated::dispatch($transaction);

        return $transaction;
    }

    public function show(Transaction $transaction): Transaction
    {
        return $transaction;
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->refresh();
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    public function listWithdrawals(User $trainer): CursorPaginator
    {
        return Withdrawal::where('trainer_id', $trainer->id)
            ->orderBy('withdrawn_at', 'desc')
            ->cursorPaginate(perPage: 15);
    }

    public function createWithdrawal(User $trainer, array $data): Withdrawal
    {
        $data['trainer_id'] = $trainer->id;

        return Withdrawal::create($data);
    }

    public function deleteWithdrawal(Withdrawal $withdrawal): void
    {
        $withdrawal->delete();
    }
}
