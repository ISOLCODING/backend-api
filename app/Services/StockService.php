<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StockService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Adjustment stok manual oleh admin/supervisor.
     * type: 'in' | 'out' | 'adjustment'
     *
     * @throws \InvalidArgumentException
     */
    public function adjust(
        int $productId,
        int $quantity,
        string $type,
        string $reason,
        int $userId,
        ?string $notes = null
    ): StockMovement {
        $product = Product::findOrFail($productId);

        if (! in_array($type, ['in', 'out', 'adjustment'])) {
            throw new \InvalidArgumentException("Tipe adjustment tidak valid: {$type}");
        }

        if ($type === 'out' && $product->stock < $quantity) {
            throw new \RuntimeException(
                "Stok tidak mencukupi. Stok saat ini: {$product->stock}, diminta: {$quantity}"
            );
        }

        return $this->stockRepository->adjustStock(
            product  : $product,
            quantity : $quantity,
            type     : $type,
            reason   : $reason,
            userId   : $userId,
            notes    : $notes
        );
    }
}
