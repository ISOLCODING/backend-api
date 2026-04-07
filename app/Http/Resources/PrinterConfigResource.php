<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrinterConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'paper_size'  => $this->paper_size ?? '58mm',
            'header_text' => $this->header_text,
            'footer_text' => $this->footer_text,
        ];
    }
}
