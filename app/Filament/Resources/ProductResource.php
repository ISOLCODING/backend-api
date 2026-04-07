<?php

namespace App\Filament\Resources;

use App\Exports\ProductsExport;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\StockMovementsRelationManager;
use App\Imports\ProductsImport;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Produk';
    protected static string|\UnitEnum|null $navigationGroup = 'Produk & Stok';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $count = Product::lowStock()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Produk')->schema([
                Grid::make(2)->schema([
                    TextInput::make('barcode')
                        ->label('Barcode')
                        ->unique(Product::class, 'barcode', ignoreRecord: true)
                        ->maxLength(50)
                        ->nullable(),

                    Select::make('category_id')
                        ->label('Kategori')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(100),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->nullable(),
            ]),

            Section::make('Harga & Stok')->schema([
                Grid::make(2)->schema([
                    TextInput::make('buy_price')
                        ->label('Harga Beli')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->minValue(0),

                    TextInput::make('sell_price')
                        ->label('Harga Jual')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->gt('buy_price'),

                    TextInput::make('stock')
                        ->label('Stok Saat Ini')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(0),

                    TextInput::make('min_stock')
                        ->label('Stok Minimum')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(5)
                        ->helperText('Peringatan stok menipis akan muncul di bawah angka ini.'),

                    TextInput::make('unit')
                        ->label('Satuan')
                        ->default('pcs')
                        ->maxLength(20),
                ]),
            ]),

            Section::make('Gambar & Status')->schema([
                FileUpload::make('image')
                    ->label('Foto Produk')
                    ->image()
                    ->directory('products')
                    ->automaticallyResizeImagesToWidth(800)
                    ->imageAspectRatio('1:1')
                    ->automaticallyCropImagesToAspectRatio()
                    ->disk('public')
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('Produk Aktif')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(asset('images/no-image.png')),

                TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                TextColumn::make('sell_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (Product $record) => match (true) {
                        $record->stock === 0            => 'danger',
                        $record->stock <= $record->min_stock => 'warning',
                        default                         => 'success',
                    })
                    ->suffix(fn (Product $record) => ' ' . $record->unit)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query) => $query->whereRaw('stock <= min_stock')),

                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ]),
                    ])
                    ->action(function (array $data): void {
                        Excel::import(new ProductsImport(), storage_path('app/public/' . $data['file']));
                        Notification::make()->title('Import berhasil!')->success()->send();
                    }),

                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => Excel::download(new ProductsExport(), 'produk-' . now()->format('Ymd') . '.xlsx')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelationManagers(): array
    {
        return [
            StockMovementsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
