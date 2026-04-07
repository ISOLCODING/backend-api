<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Terbaru';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::with(['user', 'details'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->badge(),

                TextColumn::make('details_count')
                    ->label('Item')
                    ->counts('details')
                    ->suffix(' item'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR'),

                BadgeColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->colors([
                        'success' => 'cash',
                        'info'    => 'qris',
                        'warning' => 'transfer',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger'  => 'refunded',
                    ]),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ]);
    }
}
