<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);

    Route::post('/cart/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'listOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/update/{id}', [OrderController::class, 'updateStatus']);

    Route::get('/payment/methods', [PaymentController::class, 'listMethods']);
    Route::get('/payment/link/{id}', [PaymentController::class, 'getPaymentLink']);
});
