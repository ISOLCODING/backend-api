<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => $this->product_name,
            'sell_price'   => (float) $this->sell_price,
            'buy_price'    => (float) $this->buy_price,
            'quantity'     => $this->quantity,
            'subtotal'     => (float) $this->subtotal,
            'product'      => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
