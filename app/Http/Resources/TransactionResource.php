<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'invoice_number'  => $this->invoice_number,
            'payment_method'  => $this->payment_method,
            'subtotal'        => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount'      => (float) $this->tax_amount,
            'total'           => (float) $this->total,
            'paid'            => (float) $this->paid,
            'change'          => (float) $this->change,
            'payment_status'  => $this->payment_status,
            'notes'           => $this->notes,
            'is_voided'       => $this->isVoided(),
            'void_reason'     => $this->void_reason,
            'voided_at'       => $this->voided_at?->format('Y-m-d H:i:s'),
            'cashier'         => [
                'id'   => $this->user_id,
                'name' => $this->user?->name,
            ],
            'items'           => TransactionDetailResource::collection($this->whenLoaded('details')),
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
