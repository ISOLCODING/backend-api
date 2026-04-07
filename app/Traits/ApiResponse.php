<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Response sukses untuk data tunggal.
     */
    protected function successResponse(mixed $data, string $message = 'Berhasil.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Response sukses untuk data list + pagination.
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Data berhasil diambil.'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => [
                'data'       => $paginator->items(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Response error generik.
     */
    protected function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $status);
    }

    /**
     * Response validasi gagal (422).
     */
    protected function validationErrorResponse(array $errors, string $message = 'Data tidak valid.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => ['errors' => $errors],
        ], 422);
    }

    /**
     * Response created (201).
     */
    protected function createdResponse(mixed $data, string $message = 'Data berhasil dibuat.'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Response no content (204).
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
