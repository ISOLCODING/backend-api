<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrinterConfigResource\Pages;
use App\Models\PrinterConfig;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrinterConfigResource extends Resource
{
    protected static ?string $model = PrinterConfig::class;
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Konfigurasi Printer';
    protected static string|\UnitEnum|null $navigationGroup = 'Konfigurasi';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Printer';
    protected static ?string $pluralModelLabel = 'Printer';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nama Printer')->schema([
                TextInput::make('name')
                    ->label('Nama Printer')
                    ->required()
                    ->maxLength(100),

                Select::make('printer_type')
                    ->label('Tipe Printer')
                    ->options([
                        'bluetooth' => 'Bluetooth',
                        'network'   => 'Network/LAN',
                        'usb'       => 'USB',
                    ])
                    ->required(),

                TextInput::make('device_address')
                    ->label('Alamat Perangkat')
                    ->helperText('MAC address (Bluetooth) atau IP address (Network)')
                    ->nullable(),

                Select::make('paper_size')
                    ->label('Ukuran Kertas')
                    ->options([
                        '58mm' => '58mm',
                        '80mm' => '80mm',
                    ])
                    ->default('80mm')
                    ->required(),

                Textarea::make('header_text')
                    ->label('Teks Header Struk')
                    ->rows(3)
                    ->nullable(),

                Textarea::make('footer_text')
                    ->label('Teks Footer Struk')
                    ->rows(3)
                    ->nullable(),

                Toggle::make('is_default')
                    ->label('Jadikan Printer Default')
                    ->helperText('Hanya satu printer yang bisa menjadi default.')
                    ->default(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->sortable(),
                TextColumn::make('printer_type')->label('Tipe')->badge(),
                TextColumn::make('device_address')->label('Alamat')->toggleable(),
                TextColumn::make('paper_size')->label('Kertas')->badge(),
                IconColumn::make('is_default')->label('Default')->boolean(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrinterConfigs::route('/'),
            'create' => Pages\CreatePrinterConfig::route('/create'),
            'edit'   => Pages\EditPrinterConfig::route('/{record}/edit'),
        ];
    }
}
