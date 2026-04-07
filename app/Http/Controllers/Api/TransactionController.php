<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TransactionRequest;
use App\Http\Requests\Api\VoidTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    /**
     * Daftar transaksi dengan filter dan pagination.
     * GET /api/v1/transactions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $transactions = $this->transactionService->getAll(
                filters : $request->only(['user_id', 'start_date', 'end_date', 'payment_method']),
                perPage : (int) ($request->per_page ?? 15),
            );

            return $this->paginatedResponse($transactions, 'Daftar transaksi berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Transaction index error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat transaksi.', 500);
        }
    }

    /**
     * Detail satu transaksi.
     * GET /api/v1/transactions/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $transaction = $this->transactionService->getById($id);

            return $this->successResponse(new TransactionResource($transaction));
        } catch (ModelNotFoundException) {
            return $this->errorResponse('Transaksi tidak ditemukan.', 404);
        }
    }

    /**
     * Buat transaksi baru (checkout dari POS).
     * POST /api/v1/transactions
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->checkout(
                data      : $request->validated(),
                cashierId : $request->user()->id,
            );

            return $this->createdResponse(
                new TransactionResource($transaction),
                'Transaksi berhasil dibuat.'
            );
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Transaction store error: ' . $e->getMessage());
            return $this->errorResponse('Gagal membuat transaksi.', 500);
        }
    }

    /**
     * Void (batalkan) transaksi.
     * POST /api/v1/transactions/{id}/void
     */
    public function void(VoidTransactionRequest $request, int $id): JsonResponse
    {
        try {
            $transaction = $this->transactionService->void(
                transactionId : $id,
                reason        : $request->reason,
                adminId       : $request->user()->id,
            );

            return $this->successResponse(
                new TransactionResource($transaction),
                'Transaksi berhasil di-void.'
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse('Transaksi tidak ditemukan.', 404);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Void transaction error: ' . $e->getMessage());
            return $this->errorResponse('Gagal void transaksi.', 500);
        }
    }
}
