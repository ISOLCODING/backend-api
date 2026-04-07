<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|integer|exists:products,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'discount'              => 'nullable|numeric|min:0',
            'discount_amount'       => 'nullable|numeric|min:0',
            'payment_method'        => 'required|in:cash,qris,transfer',
            'paid_amount'           => 'required_without:paid|numeric|min:0',
            'paid'                  => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'               => 'Minimal ada satu item dalam transaksi.',
            'items.*.product_id.required'  => 'ID produk wajib diisi.',
            'items.*.product_id.exists'    => 'Produk tidak ditemukan.',
            'items.*.quantity.min'         => 'Jumlah produk minimal 1.',
            'payment_method.in'            => 'Metode pembayaran tidak valid. Pilih: cash, qris, atau transfer.',
            'paid_amount.required_without' => 'Jumlah pembayaran wajib diisi.',
            'paid.min'                     => 'Jumlah pembayaran tidak valid.',
        ];
    }
}
