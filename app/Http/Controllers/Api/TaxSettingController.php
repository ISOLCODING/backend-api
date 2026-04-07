<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxSettingResource;
use App\Models\TaxSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TaxSettingController extends Controller
{
    use ApiResponse;

    /**
     * Ambil tax setting yang aktif/default.
     * GET /api/v1/tax-settings/active
     */
    public function active(): JsonResponse
    {
        $tax = TaxSetting::getActive();

        if (! $tax) {
            return $this->successResponse(null, 'Tidak ada tax setting aktif.');
        }

        return $this->successResponse(new TaxSettingResource($tax), 'Tax setting aktif.');
    }

    /**
     * List semua tax setting.
     * GET /api/v1/tax-settings
     */
    public function index(): JsonResponse
    {
        $taxes = TaxSetting::where('is_active', true)->get();

        return $this->successResponse(
            TaxSettingResource::collection($taxes),
            'Daftar tax setting berhasil diambil.'
        );
    }
}
