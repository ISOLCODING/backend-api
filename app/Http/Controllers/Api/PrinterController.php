<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrinterConfigResource;
use App\Models\PrinterConfig;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    use ApiResponse;

    /**
     * List printer yang tersedia.
     * GET /api/v1/printers
     */
    public function index(Request $request): JsonResponse
    {
        $printers = PrinterConfig::where('is_active', true)->get();

        return $this->successResponse(
            PrinterConfigResource::collection($printers),
            'Daftar printer berhasil diambil.'
        );
    }

    /**
     * Detail printer.
     * GET /api/v1/printers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $printer = PrinterConfig::find($id);

        if (! $printer) {
            return $this->errorResponse('Printer tidak ditemukan.', 404);
        }

        return $this->successResponse(new PrinterConfigResource($printer));
    }

    /**
     * Tambah printer baru.
     * POST /api/v1/printers
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'port'       => ['nullable', 'integer'],
            'is_default' => ['boolean'],
        ]);

        if ($validated['is_default'] ?? false) {
            PrinterConfig::where('is_default', true)->update(['is_default' => false]);
        }

        $printer = PrinterConfig::create($validated);

        return $this->createdResponse(new PrinterConfigResource($printer), 'Printer berhasil ditambahkan.');
    }

    /**
     * Update printer.
     * PUT /api/v1/printers/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $printer = PrinterConfig::find($id);

        if (! $printer) {
            return $this->errorResponse('Printer tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'name'       => ['sometimes', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:50'],
            'port'       => ['nullable', 'integer'],
            'is_default' => ['boolean'],
            'is_active'  => ['boolean'],
        ]);

        if ($validated['is_default'] ?? false) {
            PrinterConfig::where('is_default', true)->update(['is_default' => false]);
        }

        $printer->update($validated);

        return $this->successResponse(new PrinterConfigResource($printer), 'Printer berhasil diupdate.');
    }

    /**
     * Hapus printer.
     * DELETE /api/v1/printers/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $printer = PrinterConfig::find($id);

        if (! $printer) {
            return $this->errorResponse('Printer tidak ditemukan.', 404);
        }

        $printer->delete();

        return $this->noContentResponse();
    }
}
