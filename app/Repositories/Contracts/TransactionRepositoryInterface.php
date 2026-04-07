<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Transaction;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): Transaction;
}
