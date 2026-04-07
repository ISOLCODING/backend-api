<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total penjualan hari ini (hanya transaksi yang tidak di-void)
        $todaySales = Transaction::whereDate('created_at', Carbon::today())
            ->whereNull('voided_at')
            ->sum('total');

        // Jumlah transaksi hari ini
        $todayTransactions = Transaction::whereDate('created_at', Carbon::today())
            ->whereNull('voided_at')
            ->count();

        // Total produk aktif
        $activeProducts = Product::where('is_active', true)->count();

        // Total staff aktif (semua role kecuali super_admin)
        $activeStaff = User::where('is_active', true)
            ->whereIn('role', ['admin', 'manager', 'cashier'])
            ->count();

        // Produk dengan stok menipis
        $lowStockCount = Product::lowStock()->count();

        // Total transaksi bulan ini
        $monthlyRevenue = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereNull('voided_at')
            ->sum('total');

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($todaySales, 0, ',', '.'))
                ->description($todayTransactions . ' transaksi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart(
                    Transaction::whereDate('created_at', '>=', Carbon::now()->subDays(7))
                        ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                        ->groupBy('date')
                        ->pluck('total')
                        ->toArray()
                ),

            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description('Bulan ' . Carbon::now()->isoFormat('MMMM Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Produk Aktif', $activeProducts)
                ->description($lowStockCount > 0 ? $lowStockCount . ' stok menipis!' : 'Stok aman')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Staff Aktif', $activeStaff)
                ->description('Admin, Manager & Kasir')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
