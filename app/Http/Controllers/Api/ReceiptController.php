<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TaxSetting;
use App\Models\StoreSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    /**
     * Generate PDF Receipt for thermal printer.
     */
    public function print(int $id)
    {
        try {
            $transaction = Transaction::with(['details.product', 'user'])
                ->where('id', $id)
                ->firstOrFail();

            $settings = StoreSetting::getSettings();
            $taxSetting = TaxSetting::getActive();
            $taxRate = $taxSetting && $taxSetting->is_active ? $taxSetting->tax_rate : 11;

            $pdf = Pdf::loadView('pdf.receipt', [
                'transaction' => $transaction,
                'settings'    => $settings,
                'tax_rate'    => $taxRate
            ]);

            // Set custom paper for thermal printer (80mm width) 
            // 226.77 points is approx 80mm. 841.89 points is approx 297mm.
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); 

            return $pdf->stream("struk-{$transaction->invoice_number}.pdf");
        } catch (\Exception $e) {
            Log::error("Receipt generation error for ID #{$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat file struk.'], 500);
        }
    }
}
