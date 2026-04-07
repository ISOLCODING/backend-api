<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\StockMovement;

interface StockRepositoryInterface
{
    public function adjustStock(Product $product, int $quantity, string $type, string $reason, ?int $userId = null, ?string $reference = null, ?string $notes = null): StockMovement;

    public function decrementStock(Product $product, int $quantity, int $transactionId, int $userId): StockMovement;

    public function incrementStock(Product $product, int $quantity, int $transactionId, int $userId, string $reason = 'return'): StockMovement;
}
