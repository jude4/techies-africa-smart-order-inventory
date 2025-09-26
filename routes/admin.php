<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;

// Admin login (public)
Route::post('/login', [AdminAuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Products (admin)
    Route::apiResource('products', ProductController::class);
    Route::post('products/upload-excel', [ProductController::class, 'uploadExcel']);

    // Orders (kept for compatibility / tests)
    Route::apiResource('orders', \App\Http\Controllers\OrderController::class);

    // Reports
    Route::get('reports/low-stock', [ReportController::class, 'lowStock']);
    Route::get('reports/sales-summary', [ReportController::class, 'salesSummary']);
});
