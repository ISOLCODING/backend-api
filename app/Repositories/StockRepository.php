<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\Contracts\StockRepositoryInterface;

class StockRepository implements StockRepositoryInterface
{
    /**
     * Adjustment stok manual (in / out / adjustment).
     */
    public function adjustStock(
        Product $product,
        int $quantity,
        string $type,
        string $reason,
        ?int $userId = null,
        ?string $reference = null,
        ?string $notes = null
    ): StockMovement {
        $stockBefore = $product->stock;

        if ($type === 'in') {
            $product->increment('stock', $quantity);
        } elseif ($type === 'out') {
            $product->decrement('stock', $quantity);
        } elseif ($type === 'adjustment') {
            // quantity = stok baru
            $product->update(['stock' => $quantity]);
            $quantity = abs($quantity - $stockBefore);
        }

        $product->refresh();

        return StockMovement::create([
            'product_id'    => $product->id,
            'user_id'       => $userId,
            'transaction_id'=> null,
            'type'          => $type,
            'quantity'      => $quantity,
            'stock_before'  => $stockBefore,
            'stock_after'   => $product->stock,
            'reason'        => $reason,
            'reference'     => $reference,
            'notes'         => $notes,
        ]);
    }

    /**
     * Kurangi stok saat transaksi penjualan.
     */
    public function decrementStock(Product $product, int $quantity, int $transactionId, int $userId): StockMovement
    {
        $stockBefore = $product->stock;
        $product->decrement('stock', $quantity);

        return StockMovement::create([
            'product_id'     => $product->id,
            'user_id'        => $userId,
            'transaction_id' => $transactionId,
            'type'           => 'out',
            'quantity'       => $quantity,
            'stock_before'   => $stockBefore,
            'stock_after'    => $stockBefore - $quantity,
            'reason'         => 'sale',
            'reference'      => null,
            'notes'          => null,
        ]);
    }

    /**
     * Kembalikan stok saat void transaksi.
     */
    public function incrementStock(
        Product $product,
        int $quantity,
        int $transactionId,
        int $userId,
        string $reason = 'return'
    ): StockMovement {
        $stockBefore = $product->stock;
        $product->increment('stock', $quantity);

        return StockMovement::create([
            'product_id'     => $product->id,
            'user_id'        => $userId,
            'transaction_id' => $transactionId,
            'type'           => 'in',
            'quantity'       => $quantity,
            'stock_before'   => $stockBefore,
            'stock_after'    => $stockBefore + $quantity,
            'reason'         => $reason,
            'reference'      => null,
            'notes'          => null,
        ]);
    }
}
