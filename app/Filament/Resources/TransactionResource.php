<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers\DetailsRelationManager;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Transaksi';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Transaksi';
    protected static ?string $pluralModelLabel = 'Semua Transaksi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('invoice_number')->label('No. Invoice')->disabled(),
                Select::make('user_id')->label('Kasir')->relationship('user', 'name')->disabled(),
                TextInput::make('total')->label('Total')->prefix('Rp')->disabled(),
                TextInput::make('payment_status')->label('Status')->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->badge(),

                TextColumn::make('details_count')
                    ->label('Item')
                    ->counts('details')
                    ->suffix(' item'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'qris'    => 'info',
                        'transfer' => 'warning',
                        default => 'secondary',
                    }),

                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'refunded' => 'danger',
                        default => 'secondary',
                    }),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->options(User::whereIn('role', ['cashier', 'admin', 'manager'])->pluck('name', 'id')),

                SelectFilter::make('payment_method')
                    ->label('Metode Bayar')
                    ->options(['cash' => 'Cash', 'qris' => 'QRIS', 'transfer' => 'Transfer']),

                SelectFilter::make('payment_status')
                    ->label('Status')
                    ->options(['paid' => 'Paid', 'pending' => 'Pending', 'refunded' => 'Refunded']),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
                    ),
            ])
            ->actions([
                ViewAction::make(),

                // Void transaksi — hanya admin/manager
                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Transaction $record) => ! $record->isVoided() && Auth::user()?->isAdmin())
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('void_reason')
                            ->label('Alasan Void')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Transaction $record, array $data): void {
                        $record->update([
                            'payment_status' => 'refunded',
                            'void_reason'    => $data['void_reason'],
                            'voided_at'      => now(),
                            'voided_by'      => Auth::id(),
                        ]);

                        foreach ($record->details as $detail) {
                            $detail->product()->increment('stock', $detail->quantity);
                        }

                        Notification::make()->title('Transaksi berhasil di-void.')->success()->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelationManagers(): array
    {
        return [
            DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view'  => Pages\ViewTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Transaksi hanya bisa dibuat dari POS / API
    }
}
