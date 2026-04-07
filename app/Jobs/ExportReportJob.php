<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik (10 menit).
     */
    public int $timeout = 600;

    public function __construct(
        private readonly string  $type,      // 'pdf' | 'excel'
        private readonly string  $period,    // 'daily' | 'monthly'
        private readonly array   $filters,   // ['start_date' => ..., 'end_date' => ...]
        private readonly int     $requestedBy,
        private readonly string  $outputPath,
    ) {}

    /**
     * Eksekusi job export laporan di background queue.
     */
    public function handle(): void
    {
        Log::info("ExportReportJob started", [
            'type'         => $this->type,
            'period'       => $this->period,
            'requested_by' => $this->requestedBy,
        ]);

        try {
            if ($this->type === 'excel') {
                $this->exportExcel();
            } else {
                $this->exportPdf();
            }

            Log::info("ExportReportJob completed: {$this->outputPath}");
        } catch (\Exception $e) {
            Log::error("ExportReportJob failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export ke Excel menggunakan maatwebsite/excel (jika tersedia).
     */
    private function exportExcel(): void
    {
        // TODO: Implement Excel export menggunakan App\Exports\*
        // Contoh: Excel::store(new SalesExport($this->filters), $this->outputPath, 'local');
        Log::warning('Excel export belum diimplementasikan sepenuhnya.');
    }

    /**
     * Export ke PDF menggunakan DomPDF / Snappy.
     */
    private function exportPdf(): void
    {
        // TODO: Implement PDF export menggunakan barryvdh/laravel-dompdf
        // Contoh: $pdf = PDF::loadView('reports.sales', compact('data')); $pdf->save($this->outputPath);
        Log::warning('PDF export belum diimplementasikan sepenuhnya.');
    }

    /**
     * Handle jika job gagal setelah semua percobaan.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ExportReportJob permanently failed for user #{$this->requestedBy}: " . $exception->getMessage());
    }
}
