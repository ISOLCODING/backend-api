<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\StockMovement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';
    protected static ?string $title = 'Riwayat Stok';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Tipe')
                ->options([
                    'in'        => 'Stok Masuk',
                    'out'       => 'Stok Keluar',
                    'adj_plus'  => 'Penyesuaian +',
                    'adj_minus' => 'Penyesuaian -',
                ])
                ->required(),

            TextInput::make('quantity')
                ->label('Jumlah')
                ->numeric()
                ->required()
                ->minValue(1),

            Select::make('reason')
                ->label('Alasan')
                ->options([
                    'purchase' => 'Pembelian',
                    'sale'     => 'Penjualan',
                    'damaged'  => 'Rusak',
                    'expired'  => 'Kadaluarsa',
                    'opname'   => 'Stock Opname',
                    'other'    => 'Lainnya',
                ])
                ->required(),

            TextInput::make('reference')
                ->label('No. Referensi')
                ->maxLength(50)
                ->nullable(),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['in', 'adj_plus']),
                        'danger'  => fn ($state) => in_array($state, ['out', 'adj_minus']),
                    ]),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, StockMovement $record) =>
                        ($record->type === 'in' || $record->type === 'adj_plus' ? '+' : '-') . $state
                    ),

                TextColumn::make('stock_before')
                    ->label('Stok Sebelum'),

                TextColumn::make('stock_after')
                    ->label('Stok Sesudah'),

                TextColumn::make('reason')
                    ->label('Alasan'),

                TextColumn::make('user.name')
                    ->label('Oleh')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pergerakan')
                    ->mutateFormDataUsing(function (array $data): array {
                        $product = $this->getOwnerRecord();
                        $data['user_id']      = Auth::id();
                        $data['stock_before'] = $product->stock;
                        $data['stock_after']  = match ($data['type']) {
                            'in', 'adj_plus'   => $product->stock + $data['quantity'],
                            'out', 'adj_minus'  => max(0, $product->stock - $data['quantity']),
                            default             => $product->stock,
                        };
                        return $data;
                    })
                    ->after(function (StockMovement $record): void {
                        $this->getOwnerRecord()->update(['stock' => $record->stock_after]);
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
