<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Routes yang bisa diakses tanpa autentikasi.
| Termasuk landing page dan katalog produk.
*/

// Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Katalog produk: index (browse/search/filter) dan show (detail)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Shopping Cart Routes
    |--------------------------------------------------------------------------
    | Cart hanya untuk user yang login (auth only).
    | Cart bersifat permanent - tidak expire.
    */
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes
    |--------------------------------------------------------------------------
    | Flow: Cart → Checkout → Payment
    | Checkout page menampilkan ringkasan dan form alamat pengiriman.
    */
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    /*
    |--------------------------------------------------------------------------
    | Payment Routes
    |--------------------------------------------------------------------------
    | Flow: Checkout → Payment Method → Order History
    | User memilih metode bayar dan upload bukti (jika transfer/ewallet).
    */
    Route::get('/orders/{order}/payment', [PaymentController::class, 'create'])->name('payment.create');
    Route::post('/orders/{order}/payment', [PaymentController::class, 'store'])->name('payment.store');

    /*
    |--------------------------------------------------------------------------
    | Order Routes
    |--------------------------------------------------------------------------
    | Order history dan detail pesanan untuk user.
    | Cancel hanya bisa dilakukan jika status masih pending.
    */
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::delete('/orders/{order}', [OrderController::class, 'cancel'])->name('orders.cancel');
});

require __DIR__.'/auth.php';
