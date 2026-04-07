<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TaxSetting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly StockRepositoryInterface       $stockRepository
    ) {}

    /**
     * Ambil daftar transaksi dengan filter.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactionRepository->getAll($filters, $perPage);
    }

    /**
     * Detail transaksi by ID.
     *
     * @throws ModelNotFoundException
     */
    public function getById(int $id): Transaction
    {
        $transaction = $this->transactionRepository->findById($id);

        if (! $transaction) {
            throw new ModelNotFoundException("Transaksi dengan ID {$id} tidak ditemukan.");
        }

        return $transaction;
    }

    /**
     * Proses checkout — buat transaksi, kurangi stok, catat mutasi.
     * Semua dalam satu DB transaction untuk atomicitas.
     *
     * @throws \RuntimeException jika stok tidak cukup atau bayar kurang
     */
    public function checkout(array $data, int $cashierId): Transaction
    {
        return DB::transaction(function () use ($data, $cashierId): Transaction {
            $items    = $data['items'];
            $subtotal = 0;

            // ── 1. Validasi stok & hitung subtotal ──────────────────────
            $processedItems = [];
            foreach ($items as $item) {
                /** @var Product $product */
                $product = Product::where('id', $item['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item['quantity']) {
                    throw new \RuntimeException(
                        "Stok produk '{$product->name}' tidak mencukupi. Tersisa: {$product->stock}"
                    );
                }

                // Gunakan harga sell_price terbaru dari DB untuk keamanan
                $itemSubtotal   = $product->sell_price * $item['quantity'];
                $subtotal      += $itemSubtotal;
                $processedItems[] = compact('product', 'item', 'itemSubtotal');
            }

            // ── 2. Hitung diskon, pajak, total ───────────────────────────
            // Kita terima jumlah diskon langsung dari frontend (absolute amount)
            $discountAmount = $data['discount_amount'] ?? ($data['discount'] ?? 0);
            
            $taxSetting     = TaxSetting::getActive();
            $taxBase        = max(0, $subtotal - $discountAmount);
            
            // Hitung pajak secara konsisten
            $taxAmount      = $taxSetting ? round($taxSetting->calculateTax($taxBase)) : 0;
            $total          = $taxBase + $taxAmount;
            
            // Samakan dengan field frontend 'paid_amount'
            $paid           = $data['paid_amount'] ?? ($data['paid'] ?? 0);
            $change         = $paid - $total;

            if ($change < -1) { // Beri toleransi 1 perak untuk rounding
                throw new \RuntimeException("Jumlah pembayaran (Rp {$paid}) kurang dari total transaksi (Rp {$total}).");
            }

            // ── 3. Buat header transaksi ─────────────────────────────────
            $transaction = Transaction::create([
                'user_id'         => $cashierId,
                'invoice_number'  => Transaction::generateInvoiceNumber(),
                'payment_method'  => $data['payment_method'],
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'paid'            => $paid,
                'change'          => max(0, $change),
                'payment_status'  => 'paid',
                'notes'           => $data['notes'] ?? null,
            ]);

            // ── 4. Simpan detail + kurangi stok ─────────────────────────
            foreach ($processedItems as ['product' => $product, 'item' => $item, 'itemSubtotal' => $itemSubtotal]) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'buy_price'      => $product->buy_price,
                    'sell_price'     => $product->sell_price,
                    'quantity'       => $item['quantity'],
                    'subtotal'       => $itemSubtotal,
                ]);

                $this->stockRepository->decrementStock(
                    product       : $product,
                    quantity      : $item['quantity'],
                    transactionId : $transaction->id,
                    userId        : $cashierId,
                );
            }

            Log::info("Transaksi #{$transaction->id} berhasil dibuat oleh user #{$cashierId}");

            return $transaction->load('details.product');
        });
    }

    /**
     * Void (batalkan) transaksi — kembalikan stok.
     *
     * @throws \RuntimeException jika transaksi sudah di-void
     */
    public function void(int $transactionId, string $reason, int $adminId): Transaction
    {
        return DB::transaction(function () use ($transactionId, $reason, $adminId): Transaction {
            $transaction = $this->transactionRepository->findById($transactionId);

            if (! $transaction) {
                throw new ModelNotFoundException("Transaksi tidak ditemukan.");
            }

            if ($transaction->isVoided()) {
                throw new \RuntimeException('Transaksi sudah di-void sebelumnya.');
            }

            // Update status transaksi
            $this->transactionRepository->update($transaction, [
                'payment_status' => 'refunded',
                'void_reason'    => $reason,
                'voided_at'      => now(),
                'voided_by'      => $adminId,
            ]);

            // Kembalikan stok setiap item
            foreach ($transaction->details as $detail) {
                /** @var Product $product */
                $product = $detail->product;

                $this->stockRepository->incrementStock(
                    product       : $product,
                    quantity      : $detail->quantity,
                    transactionId : $transaction->id,
                    userId        : $adminId,
                    reason        : 'return',
                );
            }

            Log::info("Transaksi #{$transaction->id} di-void oleh user #{$adminId}");

            return $transaction->fresh()->load('details.product');
        });
    }
}
