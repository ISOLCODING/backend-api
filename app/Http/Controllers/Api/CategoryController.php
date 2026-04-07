<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * List semua kategori aktif.
     * GET /api/v1/categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Daftar kategori berhasil diambil.'
        );
    }

    /**
     * Detail kategori.
     * GET /api/v1/categories/{id}
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->errorResponse('Kategori tidak ditemukan.', 404);
        }

        return $this->successResponse(new CategoryResource($category));
    }
}
