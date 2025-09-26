<?php

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\OrderController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

// User authenticated routes (customers)
Route::middleware('auth:sanctum')->group(function () {
    // Place an order and view user's orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});
