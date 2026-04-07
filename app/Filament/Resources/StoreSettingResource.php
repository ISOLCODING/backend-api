<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreSettingResource\Pages;
use App\Models\StoreSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoreSettingResource extends Resource
{
    protected static ?string $model = StoreSetting::class;
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static string|\UnitEnum|null $navigationGroup = 'Konfigurasi';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Pengaturan Toko';
    protected static ?string $pluralModelLabel = 'Pengaturan Toko';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Toko')->schema([
                Grid::make(2)->schema([
                    TextInput::make('store_name')
                        ->label('Nama Toko')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('store_phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->maxLength(20)
                        ->nullable(),

                    TextInput::make('store_email')
                        ->label('Email Toko')
                        ->email()
                        ->maxLength(100)
                        ->nullable(),

                    TextInput::make('store_address')
                        ->label('Alamat Toko')
                        ->required()
                        ->maxLength(255),
                ]),

                FileUpload::make('store_logo')
                    ->label('Logo Toko')
                    ->image()
                    ->directory('settings')
                    ->nullable(),
            ]),

            Section::make('Mata Uang & Regional')->schema([
                Grid::make(3)->schema([
                    TextInput::make('currency')
                        ->label('Mata Uang')
                        ->default('IDR')
                        ->required(),

                    TextInput::make('currency_symbol')
                        ->label('Simbol')
                        ->default('Rp')
                        ->required(),

                    TextInput::make('timezone')
                        ->label('Timezone')
                        ->default('Asia/Jakarta')
                        ->required(),
                ]),
            ]),

            Section::make('Format Struk/Invoice')->schema([
                Grid::make(2)->schema([
                    TextInput::make('invoice_prefix')
                        ->label('Prefix Invoice')
                        ->default('INV')
                        ->required(),

                    TextInput::make('invoice_digits')
                        ->label('Jumlah Digit Nomor')
                        ->numeric()
                        ->default(6)
                        ->required(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('store_logo')->label('Logo')->circular(),
                TextColumn::make('store_name')->label('Nama Toko')->weight('bold'),
                TextColumn::make('store_phone')->label('Telepon'),
                TextColumn::make('currency')->label('Mata Uang'),
                TextColumn::make('invoice_prefix')->label('Prefix'),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreSettings::route('/'),
            'create' => Pages\CreateStoreSetting::route('/create'),
            'edit' => Pages\EditStoreSetting::route('/{record}/edit'),
        ];
    }
}
