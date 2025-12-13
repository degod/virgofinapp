<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Order\CancelOrderController;
use App\Http\Controllers\Order\CreateOrderController;
use App\Http\Controllers\Order\ListOrdersController;
use App\Http\Controllers\Order\OrderSideOptionController;
use App\Http\Controllers\Order\OrderSymbolOptionController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class)->name('auth.login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', ProfileController::class)->name('user.profile');

    Route::get('/orders', ListOrdersController::class)->name('orders.list');
    Route::post('/orders', CreateOrderController::class)->name('orders.create');
    Route::post('/orders/{id}/cancel', CancelOrderController::class)->name('orders.cancel');
    Route::get('/orders/sides', OrderSideOptionController::class)->name('orders.sides');
    Route::get('/orders/symbols', OrderSymbolOptionController::class)->name('orders.symbols');
});
