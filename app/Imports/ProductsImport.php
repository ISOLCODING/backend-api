<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $categoryId = Category::where('name', $row['kategori'])->value('id');

            Product::updateOrCreate(
                ['barcode' => $row['barcode']],
                [
                    'name'        => $row['nama_produk'],
                    'category_id' => $categoryId,
                    'buy_price'   => (float) str_replace([',', '.'], ['', ''], $row['harga_beli']),
                    'sell_price'  => (float) str_replace([',', '.'], ['', ''], $row['harga_jual']),
                    'stock'       => (int) $row['stok'],
                    'unit'        => $row['satuan'] ?? 'pcs',
                    'is_active'   => true,
                ]
            );
        }
    }

    public function rules(): array
    {
        return [
            '*.nama_produk' => 'required|string',
            '*.harga_jual'  => 'required|numeric',
        ];
    }
}
