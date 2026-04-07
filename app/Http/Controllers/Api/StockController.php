<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StockAdjustmentRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly StockService $stockService
    ) {}

    /**
     * Produk dengan stok di bawah minimum.
     * GET /api/v1/stocks/low
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::with('category')->lowStock()->orderBy('stock')->get();

        return $this->successResponse(
            ProductResource::collection($products),
            'Daftar produk stok kritis.'
        );
    }

    /**
     * Riwayat mutasi stok per produk.
     * GET /api/v1/stocks/{productId}/history
     */
    public function history(Request $request, int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (! $product) {
            return $this->errorResponse('Produk tidak ditemukan.', 404);
        }

        $movements = StockMovement::with(['user:id,name', 'product:id,name'])
            ->where('product_id', $productId)
            ->latest()
            ->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($movements, "Riwayat stok produk '{$product->name}'.");
    }

    /**
     * Adjustment stok manual.
     * POST /api/v1/stocks/{productId}/adjust
     */
    public function adjust(StockAdjustmentRequest $request, int $productId): JsonResponse
    {
        try {
            $movement = $this->stockService->adjust(
                productId : $productId,
                quantity  : $request->quantity,
                type      : $request->type,
                reason    : $request->reason,
                userId    : $request->user()->id,
                notes     : $request->notes,
            );

            return $this->createdResponse(
                new StockMovementResource($movement->load(['product', 'user'])),
                'Stok berhasil disesuaikan.'
            );
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Stock adjust error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menyesuaikan stok.', 500);
        }
    }
}
