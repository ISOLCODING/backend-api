<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Models\PrinterConfig;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\StoreSettingResource;
use App\Http\Resources\TaxSettingResource;
use App\Http\Resources\PrinterConfigResource;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Ambil semua pengaturan (Toko, Pajak, Printer).
     * GET /api/v1/settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'store'   => new StoreSettingResource(StoreSetting::getSettings()),
                'tax'     => new TaxSettingResource(TaxSetting::getActive()),
                'printer' => PrinterConfig::where('is_default', true)->first() 
                            ? new PrinterConfigResource(PrinterConfig::where('is_default', true)->first())
                            : null,
            ]
        ]);
    }

    /**
     * Update pengaturan toko.
     * POST /api/v1/settings/store
     */
    public function updateStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_name'    => 'required|string|max:255',
            'store_address' => 'nullable|string',
            'store_phone'   => 'nullable|string|max:20',
            'store_email'   => 'nullable|email|max:255',
        ]);

        $settings = StoreSetting::getSettings();
        $settings->update($validated);

        return response()->json(['success' => true, 'data' => $settings]);
    }

    /**
     * Update pengaturan pajak.
     * POST /api/v1/settings/tax
     */
    public function updateTax(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tax_name'  => 'required|string|max:50',
            'tax_rate'  => 'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        $tax = TaxSetting::where('is_active', true)->first() ?? new TaxSetting();
        $tax->fill($validated);
        $tax->is_active = true; // Selalu aktifkan jika diupdate via POS
        $tax->save();

        return response()->json(['success' => true, 'data' => $tax]);
    }

    /**
     * Ambil konfigurasi printer default.
     * GET /api/v1/settings/printer
     */
    public function printer(): JsonResponse
    {
        $printer = PrinterConfig::where('is_default', true)->first();
        return response()->json(['success' => true, 'data' => $printer]);
    }
}
