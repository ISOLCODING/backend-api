<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'barcode'     => $this->barcode,
            'description' => $this->description,
            'sell_price'  => (float) $this->sell_price,
            'stock'       => $this->stock,
            'min_stock'   => $this->min_stock,
            'unit'        => $this->unit,
            'image'       => $this->image_url,
            'is_active'   => (bool) $this->is_active,
            'is_low_stock'=> $this->isLowStock(),
            'category'    => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
