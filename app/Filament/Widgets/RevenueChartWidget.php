<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Penjualan 7 Hari Terakhir';
    protected static ?int $sort = 2;
    protected string $color = 'primary';

    protected function getData(): array
    {
        // Query penjualan harian 7 hari terakhir
        $data = Transaction::whereDate('created_at', '>=', Carbon::now()->subDays(6))
            ->whereNull('voided_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(id) as total_transactions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Isi tanggal yang tidak ada transaksi dengan 0
        $labels = [];
        $revenues = [];
        $transactions = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayData = $data->firstWhere('date', $date->format('Y-m-d'));

            $labels[]       = $date->isoFormat('ddd, DD MMM');
            $revenues[]     = $dayData ? (float) $dayData->revenue : 0;
            $transactions[] = $dayData ? (int) $dayData->total_transactions : 0;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (Rp)',
                    'data'            => $revenues,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderColor'     => 'rgb(251, 191, 36)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Jumlah Transaksi',
                    'data'            => $transactions,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor'     => 'rgb(99, 102, 241)',
                    'borderWidth'     => 2,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y'  => ['type' => 'linear', 'display' => true, 'position' => 'left'],
                'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }
}
