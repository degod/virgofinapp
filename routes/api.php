<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Order\CreateOrderController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class)->name('auth.login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', ProfileController::class)->name('user.profile');
    Route::post('/orders', CreateOrderController::class)->name('orders.create');
});
