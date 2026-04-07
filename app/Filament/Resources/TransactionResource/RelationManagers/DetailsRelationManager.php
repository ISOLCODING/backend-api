<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $title = 'Detail Item';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Produk'),

                TextColumn::make('sell_price')
                    ->label('Harga Satuan')
                    ->money('IDR'),

                TextColumn::make('quantity')
                    ->label('Qty'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
