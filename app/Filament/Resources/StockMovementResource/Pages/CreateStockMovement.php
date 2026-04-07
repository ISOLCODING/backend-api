<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil stok produk sekarang sebelum pergerakan dicatat
        $product = \App\Models\Product::findOrFail($data['product_id']);
        $data['user_id']      = Auth::id();
        $data['stock_before'] = $product->stock;
        $data['stock_after']  = match ($data['type']) {
            'in', 'adj_plus'   => $product->stock + $data['quantity'],
            'out', 'adj_minus' => max(0, $product->stock - $data['quantity']),
            default            => $product->stock,
        };
        return $data;
    }

    protected function afterCreate(): void
    {
        // Update stok produk setelah record disimpan
        $this->record->product()->update(['stock' => $this->record->stock_after]);
    }
}
