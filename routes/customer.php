<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/shop', [CatalogController::class, 'index'])->name('shop.index');

Route::middleware(['auth', 'customer'])
    ->group(function (): void {
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');

        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    });
