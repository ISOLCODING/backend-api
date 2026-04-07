<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Pergerakan Stok';
    protected static string|\UnitEnum|null $navigationGroup = 'Produk & Stok';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Pergerakan Stok';
    protected static ?string $pluralModelLabel = 'Pergerakan Stok';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('Tipe Pergerakan')
                    ->options([
                        'in'        => 'Stok Masuk',
                        'out'       => 'Stok Keluar',
                        'adj_plus'  => 'Penyesuaian +',
                        'adj_minus' => 'Penyesuaian -',
                        'transfer'  => 'Transfer',
                        'return'    => 'Retur',
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
                        'purchase' => 'Pembelian dari Supplier',
                        'sale'     => 'Penjualan',
                        'damaged'  => 'Barang Rusak',
                        'expired'  => 'Kadaluarsa',
                        'opname'   => 'Stock Opname',
                        'transfer' => 'Transfer Gudang',
                        'return'   => 'Retur ke Supplier',
                        'other'    => 'Lainnya',
                    ])
                    ->required(),

                TextInput::make('reference')
                    ->label('No. Referensi / Invoice')
                    ->maxLength(50)
                    ->nullable(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['in', 'adj_plus']),
                        'danger'  => fn ($state) => in_array($state, ['out', 'adj_minus']),
                        'info'    => 'transfer',
                        'warning' => 'return',
                    ]),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable(),

                TextColumn::make('stock_before')
                    ->label('Stok Sebelum'),

                TextColumn::make('stock_after')
                    ->label('Stok Sesudah'),

                TextColumn::make('reason')
                    ->label('Alasan'),

                TextColumn::make('reference')
                    ->label('Referensi')
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Oleh')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'in'        => 'Stok Masuk',
                        'out'       => 'Stok Keluar',
                        'adj_plus'  => 'Penyesuaian +',
                        'adj_minus' => 'Penyesuaian -',
                    ]),

                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
        ];
    }
}
