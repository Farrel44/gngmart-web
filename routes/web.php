<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
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

// Kategori: tampilkan produk per kategori (slug-based URL)
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Pencarian: live suggestions (JSON), popular/rekomendasi (JSON), dan halaman hasil pencarian
Route::get('/search', [SearchController::class, 'results'])->name('search.results');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/popular', [SearchController::class, 'popular'])->name('search.popular');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile/show', [ProfileController::class, 'store'])->name('profile.store');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
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
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{item}/quantity', [CartController::class, 'updateQuantity'])->name('cart.updateQuantity');
    Route::delete('/cart/bulk', [CartController::class, 'bulkDelete'])->name('cart.bulkDelete');
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

/*
|--------------------------------------------------------------------------
| Midtrans Webhook
|--------------------------------------------------------------------------
| Endpoint untuk menerima notification dari Midtrans.
| Harus bisa diakses publik tanpa auth dan tanpa CSRF.
*/
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
    ->name('midtrans.notification');

require __DIR__.'/auth.php';
