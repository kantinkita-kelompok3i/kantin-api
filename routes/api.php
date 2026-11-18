<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
| KANTINKU API v1 — 26 endpoint aktif
| Total route di prefix /api/v1 (cek: php artisan route:list --path=api/v1)
*/

Route::prefix('v1')->group(function () {
    // Publik — tanpa token
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);

    // Butuh token (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('users/me', [UserController::class, 'show']);
        Route::patch('users/me', [UserController::class, 'update']);

        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart/items', [CartController::class, 'store']);
        Route::patch('cart/items/{item}', [CartController::class, 'update']);
        Route::delete('cart/items/{item}', [CartController::class, 'destroy']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::delete('orders/{order}', [OrderController::class, 'destroy']);
        Route::get('orders/{order}/status-history', [OrderController::class, 'statusHistory']);
        Route::post('orders/{order}/payments', [PaymentController::class, 'store']);
        Route::get('orders/{order}/payment', [PaymentController::class, 'show']);

        // Admin saja
        Route::middleware('admin')->group(function () {
            Route::post('categories', [CategoryController::class, 'store']);
            Route::patch('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

            Route::post('products', [ProductController::class, 'store']);
            Route::patch('products/{product}', [ProductController::class, 'update']);
            Route::delete('products/{product}', [ProductController::class, 'destroy']);

            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
            Route::patch('payments/{payment}/paid', [PaymentController::class, 'markPaid']);
            Route::get('admin/orders', [OrderController::class, 'adminIndex']);
            Route::get('admin/summary', [DashboardController::class, 'summary']);
        });
    });
});
