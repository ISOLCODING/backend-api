<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 5 Produk Terlaris (30 Hari Terakhir)';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->addSelect(DB::raw('COALESCE(SUM(td.quantity), 0) as total_sold'))
                    ->addSelect(DB::raw('COALESCE(SUM(td.subtotal), 0) as total_revenue'))
                    ->leftJoin('transaction_details as td', 'products.id', '=', 'td.product_id')
                    ->leftJoin('transactions as t', function ($join) {
                        $join->on('td.transaction_id', '=', 't.id')
                             ->whereNull('t.voided_at')
                             ->where('t.created_at', '>=', Carbon::now()->subDays(30));
                    })
                    ->groupBy('products.id')
                    ->orderByDesc('total_sold')
                    ->limit(5)
            )
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/no-image.png')),

                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge(),

                TextColumn::make('total_sold')
                    ->label('Terjual')
                    ->suffix(' pcs')
                    ->sortable(),

                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->badge()
                    ->color(fn (Product $record) => $record->isLowStock() ? 'danger' : 'success'),
            ]);
    }
}
