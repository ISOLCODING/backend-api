<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Ambil semua produk aktif dengan filter, search, dan pagination.
     * Hasil di-cache selama 5 menit.
     */
    public function getAllActive(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search     = $filters['search'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $barcode    = $filters['barcode'] ?? null;

        return Product::with('category')
            ->active()
            ->when($search, function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('name', 'LIKE', "%{$value}%")
                      ->orWhere('barcode', 'LIKE', "%{$value}%");
                });
            })
            ->when($categoryId, function ($query, $value) {
                // Ambil ID kategori terpilih dan semua ID anak-anaknya (sub-kategori)
                $categoryIds = \App\Models\Category::where('id', $value)
                    ->orWhere('parent_id', $value)
                    ->pluck('id')
                    ->toArray();
                
                $query->whereIn('category_id', $categoryIds);
            })
            ->when($barcode, fn ($q, $v) => $q->where('barcode', $v))
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Cari produk aktif by ID.
     */
    public function findById(int $id): ?Product
    {
        return Cache::remember("product:{$id}", now()->addMinutes(10), function () use ($id) {
            return Product::with('category')->active()->find($id);
        });
    }

    /**
     * Cari produk berdasarkan barcode.
     */
    public function findByBarcode(string $barcode): ?Product
    {
        return Product::with('category')
            ->where('barcode', $barcode)
            ->active()
            ->first();
    }

    /**
     * Ambil produk dengan stok di bawah minimum.
     */
    public function getLowStock(): Collection
    {
        return Product::with('category')
            ->lowStock()
            ->orderBy('stock')
            ->get();
    }

    /**
     * Invalidasi cache produk setelah ada perubahan.
     */
    public function invalidateCache(int $id): void
    {
        Cache::forget("product:{$id}");
        Cache::forget('products:active');
    }
}
