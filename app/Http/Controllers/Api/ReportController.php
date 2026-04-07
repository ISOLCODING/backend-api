<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Laporan penjualan harian.
     * GET /api/v1/reports/daily?date=2026-04-04
     */
    public function daily(Request $request): JsonResponse
    {
        try {
            $result = $this->reportService->daily($request->date);

            return $this->successResponse($result, 'Laporan harian berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Daily report error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat laporan harian.', 500);
        }
    }

    /**
     * Laporan penjualan bulanan.
     * GET /api/v1/reports/monthly?month=4&year=2026
     */
    public function monthly(Request $request): JsonResponse
    {
        try {
            $result = $this->reportService->monthly(
                month : $request->month ? (int) $request->month : null,
                year  : $request->year  ? (int) $request->year  : null,
            );

            return $this->successResponse($result, 'Laporan bulanan berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Monthly report error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat laporan bulanan.', 500);
        }
    }

    /**
     * Top produk terlaris.
     * GET /api/v1/reports/top-products?limit=10&start_date=&end_date=
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $result = $this->reportService->topProducts(
                limit     : (int) ($request->limit ?? 10),
                startDate : $request->start_date,
                endDate   : $request->end_date,
            );

            return $this->successResponse($result, 'Top produk berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Top products error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat laporan produk.', 500);
        }
    }

    /**
     * Data grafik penjualan.
     * GET /api/v1/reports/sales-chart?period=week|month|year
     */
    public function salesChart(Request $request): JsonResponse
    {
        $request->validate(['period' => ['in:week,month,year']]);

        try {
            $result = $this->reportService->salesChart($request->period ?? 'week');

            return $this->successResponse($result, 'Data grafik berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Sales chart error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat data grafik.', 500);
        }
    }

    /**
     * Laporan profit (omset - HPP).
     * GET /api/v1/reports/profit?start_date=&end_date=
     */
    public function profit(Request $request): JsonResponse
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate   = $request->end_date   ?? Carbon::today()->format('Y-m-d');

        try {
            $result = $this->reportService->profit($startDate, $endDate);

            return $this->successResponse($result, 'Laporan profit berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Profit report error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memuat laporan profit.', 500);
        }
    }
}
