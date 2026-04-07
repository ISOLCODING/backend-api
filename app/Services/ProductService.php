<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Ambil semua produk aktif (dengan filter & pagination).
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getAllActive($filters, $perPage);
    }

    /**
     * Detail produk by ID.
     *
     * @throws ModelNotFoundException
     */
    public function getById(int $id): object
    {
        $product = $this->productRepository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException("Produk dengan ID {$id} tidak ditemukan.");
        }

        return $product;
    }

    /**
     * Cari produk berdasarkan barcode (untuk scan POS).
     *
     * @throws ModelNotFoundException
     */
    public function findByBarcode(string $barcode): object
    {
        $product = $this->productRepository->findByBarcode($barcode);

        if (! $product) {
            throw new ModelNotFoundException("Produk dengan barcode '{$barcode}' tidak ditemukan.");
        }

        return $product;
    }

    /**
     * Produk dengan stok di bawah minimum.
     */
    public function getLowStock(): Collection
    {
        return $this->productRepository->getLowStock();
    }
}
