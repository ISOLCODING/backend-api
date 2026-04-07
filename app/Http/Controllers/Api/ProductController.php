<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * Daftar produk aktif dengan search, filter, pagination.
     * GET /api/v1/products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $products = $this->productService->getAll(
                filters : $request->only(['search', 'category_id', 'barcode']),
                perPage : (int) ($request->per_page ?? 16),
            );

            // Transformasikan koleksi item ke Resource
            $products->setCollection(
                $products->getCollection()->map(fn ($product) => new ProductResource($product))
            );

            return $this->paginatedResponse($products, 'Daftar produk berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Product index error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat produk.', 500);
        }
    }

    /**
     * Detail satu produk.
     * GET /api/v1/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getById($id);

            return $this->successResponse(new ProductResource($product));
        } catch (ModelNotFoundException) {
            return $this->errorResponse('Produk tidak ditemukan.', 404);
        }
    }

    /**
     * Cari produk berdasarkan barcode (untuk scan POS).
     * GET /api/v1/products/barcode/{barcode}
     */
    public function findByBarcode(string $barcode): JsonResponse
    {
        try {
            $product = $this->productService->findByBarcode($barcode);

            return $this->successResponse(new ProductResource($product));
        } catch (ModelNotFoundException) {
            return $this->errorResponse('Produk tidak ditemukan.', 404);
        }
    }

    /**
     * Produk dengan stok di bawah minimum.
     * GET /api/v1/products/low-stock
     */
    public function lowStock(): JsonResponse
    {
        try {
            $products = $this->productService->getLowStock();

            return $this->successResponse(
                ProductResource::collection($products),
                'Daftar produk stok kritis.'
            );
        } catch (\Exception $e) {
            Log::error('Low stock error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat data stok.', 500);
        }
    }
}
