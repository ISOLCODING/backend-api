<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'store_name'     => $this->store_name,
            'store_address'  => $this->store_address,
            'store_phone'    => $this->store_phone,
            'store_email'    => $this->store_email,
            'store_logo'     => $this->store_logo,
            'currency'       => $this->currency,
            'currency_symbol'=> $this->currency_symbol,
            'timezone'       => $this->timezone,
            'invoice_prefix' => $this->invoice_prefix,
            'invoice_digits' => (int) $this->invoice_digits,
        ];
    }
}
