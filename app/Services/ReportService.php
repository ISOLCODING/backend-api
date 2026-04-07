<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Laporan harian — ringkasan + daftar transaksi.
     */
    public function daily(?string $dateStr = null): array
    {
        $date = $dateStr ? Carbon::parse($dateStr) : Carbon::today();

        $baseQuery = Transaction::whereDate('created_at', $date)->whereNull('voided_at');

        $summary = [
            'date'               => $date->format('Y-m-d'),
            'total_transactions' => (clone $baseQuery)->count(),
            'total_revenue'      => (float) (clone $baseQuery)->sum('total'),
            'total_items_sold'   => (int) TransactionDetail::whereHas(
                'transaction',
                fn ($q) => $q->whereDate('created_at', $date)->whereNull('voided_at')
            )->sum('quantity'),
            'total_discount'     => (float) (clone $baseQuery)->sum('discount_amount'),
            'total_tax'          => (float) (clone $baseQuery)->sum('tax_amount'),
        ];

        $transactions = Transaction::with('user:id,name')
            ->whereDate('created_at', $date)
            ->whereNull('voided_at')
            ->latest()
            ->get(['id', 'invoice_number', 'user_id', 'total', 'payment_method', 'created_at']);

        return compact('summary', 'transactions');
    }

    /**
     * Laporan bulanan — ringkasan per bulan.
     */
    public function monthly(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? Carbon::now()->month;
        $year  = $year ?? Carbon::now()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

        $baseQuery = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('voided_at');

        $totalTransactions = (clone $baseQuery)->count();
        $totalRevenue      = (float) (clone $baseQuery)->sum('total');
        $daysInMonth       = $endDate->day;

        return [
            'period'             => Carbon::create($year, $month)->isoFormat('MMMM YYYY'),
            'total_transactions' => $totalTransactions,
            'total_revenue'      => $totalRevenue,
            'average_per_day'    => $totalTransactions > 0
                ? round($totalRevenue / $daysInMonth, 2)
                : 0,
            'total_discount'     => (float) (clone $baseQuery)->sum('discount_amount'),
            'total_tax'          => (float) (clone $baseQuery)->sum('tax_amount'),
        ];
    }

    /**
     * Top produk terlaris dalam rentang waktu.
     */
    public function topProducts(int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        return TransactionDetail::select(
            'product_id',
            'product_name',
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                $q->whereNull('voided_at')
                  ->when($startDate, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                  ->when($endDate, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Data untuk grafik penjualan (week | month | year).
     */
    public function salesChart(string $period = 'week'): array
    {
        $labels = [];
        $data   = [];

        match ($period) {
            'week' => (function () use (&$labels, &$data): void {
                for ($i = 6; $i >= 0; $i--) {
                    $date     = Carbon::now()->subDays($i);
                    $labels[] = $date->isoFormat('ddd, DD MMM');
                    $data[]   = (float) Transaction::whereDate('created_at', $date)->whereNull('voided_at')->sum('total');
                }
            })(),

            'month' => (function () use (&$labels, &$data): void {
                for ($i = 29; $i >= 0; $i--) {
                    $date     = Carbon::now()->subDays($i);
                    $labels[] = $date->format('d M');
                    $data[]   = (float) Transaction::whereDate('created_at', $date)->whereNull('voided_at')->sum('total');
                }
            })(),

            'year' => (function () use (&$labels, &$data): void {
                for ($i = 11; $i >= 0; $i--) {
                    $date     = Carbon::now()->subMonths($i);
                    $labels[] = $date->isoFormat('MMM YYYY');
                    $data[]   = (float) Transaction::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->whereNull('voided_at')
                        ->sum('total');
                }
            })(),

            default => null,
        };

        return compact('labels', 'data');
    }

    /**
     * Laporan profit (omset - HPP).
     */
    public function profit(string $startDate, string $endDate): array
    {
        $totalRevenue = (float) Transaction::whereNull('voided_at')
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->sum('total');

        $totalCost = (float) TransactionDetail::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereNull('voided_at')
              ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        })
            ->select(DB::raw('SUM(buy_price * quantity) as cost'))
            ->value('cost') ?? 0;

        $grossProfit  = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;

        return [
            'period'        => ['start' => $startDate, 'end' => $endDate],
            'total_revenue' => $totalRevenue,
            'total_cost'    => $totalCost,
            'gross_profit'  => $grossProfit,
            'profit_margin' => $profitMargin,
        ];
    }
}
