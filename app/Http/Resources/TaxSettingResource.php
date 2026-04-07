<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tax_name'  => $this->tax_name,
            'tax_rate'  => (float) $this->tax_rate,
            'tax_type'  => $this->tax_type,
            'rounding'  => $this->rounding,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
