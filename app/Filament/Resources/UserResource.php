<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
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
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Staff';
    protected static string|\UnitEnum|null $navigationGroup = 'Konfigurasi';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Staff';
    protected static ?string $pluralModelLabel = 'Staff';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Staff')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->maxLength(15)
                        ->nullable(),

                    Select::make('role')
                        ->label('Role')
                        ->options([
                            'admin'     => 'Admin Toko',
                            'cashier'   => 'Kasir',
                        ])
                        ->required(),

                    TextInput::make('pin_code')
                        ->label('PIN Code')
                        ->helperText('6 digit PIN untuk login cepat di aplikasi kasir.')
                        ->maxLength(6)
                        ->minLength(6)
                        ->password()
                        ->nullable()
                        ->dehydrated(fn ($state) => filled($state)),

                    Toggle::make('is_active')
                        ->label('Staff Aktif')
                        ->default(true),
                ]),

                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(2)
                    ->nullable(),
            ]),

            Section::make('Password')->schema([
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Kosongkan jika tidak ingin mengubah password.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'cashier' => 'success',
                        default => 'secondary',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('transactions_count')
                    ->label('Transaksi')
                    ->counts('transactions')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin'   => 'Admin Toko',
                        'cashier' => 'Kasir',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
