<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-stock') ?? false;
    }

    public function rules(): array
    {
        return [
            'type'     => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason'   => ['required', 'string', 'max:255'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'     => 'Tipe adjustment wajib diisi.',
            'type.in'           => 'Tipe adjustment harus in, out, atau adjustment.',
            'quantity.required' => 'Jumlah stok wajib diisi.',
            'quantity.min'      => 'Jumlah stok minimal 1.',
            'reason.required'   => 'Alasan adjustment wajib diisi.',
        ];
    }
}
