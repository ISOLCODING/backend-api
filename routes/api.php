<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PrinterController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TaxSettingController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ReceiptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| KASIRIN AJA — API Routes v1
|--------------------------------------------------------------------------
|
| Rate limiting: 60 request per menit per IP (throttle:60,1)
| Authentication: Laravel Sanctum (Bearer Token)
| Format response: JSON (lihat App\Traits\ApiResponse)
|
*/

Route::prefix('v1')->group(function (): void {

    // =================================================================
    // PUBLIC ROUTES — tidak perlu token
    // =================================================================
    Route::prefix('auth')->middleware('throttle:60,1')->group(function (): void {
        Route::post('login',     [AuthController::class, 'login']);
        Route::post('login-pin', [AuthController::class, 'loginWithPin']);
    });

    // =================================================================
    // PROTECTED ROUTES — wajib Bearer Token Sanctum
    // =================================================================
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {

        // ── Auth ──────────────────────────────────────────────────────
        Route::prefix('auth')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me',      [AuthController::class, 'me']);
        });

        // ── Products ──────────────────────────────────────────────────
        Route::prefix('products')->group(function (): void {
            Route::get('low-stock',        [ProductController::class, 'lowStock']);       // Sebelum {id}!
            Route::get('barcode/{barcode}', [ProductController::class, 'findByBarcode']); // Sebelum {id}!
            Route::get('/',                [ProductController::class, 'index']);
            Route::get('{id}',             [ProductController::class, 'show']);
        });

        // ── Categories ────────────────────────────────────────────────
        Route::prefix('categories')->group(function (): void {
            Route::get('/',     [CategoryController::class, 'index']);
            Route::get('{id}',  [CategoryController::class, 'show']);
        });

        // ── Transactions ──────────────────────────────────────────────
        Route::prefix('transactions')->group(function (): void {
            Route::get('/',          [TransactionController::class, 'index']);
            Route::post('/',         [TransactionController::class, 'store']);
            Route::get('{id}',       [TransactionController::class, 'show']);
            Route::post('{id}/void', [TransactionController::class, 'void']);
            Route::get('{id}/receipt', [ReceiptController::class, 'print']);
        });

        // ── Stocks ────────────────────────────────────────────────────
        Route::prefix('stocks')->group(function (): void {
            Route::get('low',                   [StockController::class, 'lowStock']);
            Route::get('{productId}/history',    [StockController::class, 'history']);
            Route::post('{productId}/adjust',    [StockController::class, 'adjust']);
        });

        // ── Reports ───────────────────────────────────────────────────
        Route::prefix('reports')->group(function (): void {
            Route::get('daily',       [ReportController::class, 'daily']);
            Route::get('monthly',     [ReportController::class, 'monthly']);
            Route::get('top-products',[ReportController::class, 'topProducts']);
            Route::get('sales-chart', [ReportController::class, 'salesChart']);
            Route::get('profit',      [ReportController::class, 'profit']);
        });

        // ── Tax Settings ──────────────────────────────────────────────
        Route::prefix('tax-settings')->group(function (): void {
            Route::get('/',       [TaxSettingController::class, 'index']);
            Route::get('active',  [TaxSettingController::class, 'active']);
        });

        // ── Printers ──────────────────────────────────────────────────
        Route::prefix('printers')->group(function (): void {
            Route::get('/',        [PrinterController::class, 'index']);
            Route::post('/',       [PrinterController::class, 'store']);
            Route::get('{id}',     [PrinterController::class, 'show']);
            Route::put('{id}',     [PrinterController::class, 'update']);
            Route::delete('{id}',  [PrinterController::class, 'destroy']);
        });

        // ── Settings ──────────────────────────────────────────────────
        Route::prefix('settings')->group(function (): void {
            Route::get('/', [SettingController::class, 'index']);
            Route::post('store', [SettingController::class, 'updateStore']);
            Route::post('tax', [SettingController::class, 'updateTax']);
        });
    });

});

// Backwards compatibility — redirect /api/* ke /api/v1/*
// (opsional, bisa dihapus setelah Flutter diupdate)
Route::prefix('auth')->middleware('throttle:60,1')->group(function (): void {
    Route::post('login',     [AuthController::class, 'login']);
    Route::post('login-pin', [AuthController::class, 'loginWithPin']);
});
