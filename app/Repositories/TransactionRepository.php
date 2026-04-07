<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Ambil semua transaksi dengan filter dan pagination.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $userId    = $filters['user_id'] ?? null;
        $startDate = $filters['start_date'] ?? null;
        $endDate   = $filters['end_date'] ?? null;
        $method    = $filters['payment_method'] ?? null;

        return Transaction::with(['user:id,name', 'details.product:id,name'])
            ->notVoided()
            ->dateRange($startDate, $endDate)
            ->when($userId, fn ($q, $v) => $q->where('user_id', $v))
            ->when($method, fn ($q, $v) => $q->where('payment_method', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Cari transaksi by ID beserta relasi lengkap.
     */
    public function findById(int $id): ?Transaction
    {
        return Transaction::with([
            'user:id,name,email',
            'details.product',
            'voidedBy:id,name',
        ])->find($id);
    }

    /**
     * Buat transaksi baru.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update data transaksi.
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh();
    }
}
