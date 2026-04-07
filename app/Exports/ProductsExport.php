<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Product::with('category')->orderBy('name');
    }

    public function headings(): array
    {
        return ['No', 'Barcode', 'Nama Produk', 'Kategori', 'Harga Beli', 'Harga Jual', 'Stok', 'Satuan', 'Status'];
    }

    public function map($product): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $product->barcode,
            $product->name,
            $product->category?->name ?? '-',
            $product->buy_price,
            $product->sell_price,
            $product->stock,
            $product->unit,
            $product->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
